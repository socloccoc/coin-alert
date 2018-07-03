<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\LineUserInterface;
use App\Repository\Contracts\EmailsImportInterface;
use App\Services\LineService;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;
use App\Repository\Contracts\LineBotAccountInterface;

class LineUserController extends Controller
{
    //
    protected $lineUser;
    protected $lineService;
    protected $emailsImport;

    public function __construct(
        LineUserInterface $lineUser,
        LineService $lineService,
        EmailsImportInterface $emailsImport,
        LineBotAccountInterface $lineBotAccount
    ) {
        $this->lineUser = $lineUser;
        $this->lineService = $lineService;
        $this->emailsImport = $emailsImport;
        $this->lineBotAccount = $lineBotAccount;
    }

    public function index(Request $request)
    {
        $result = $this->lineUser->all();
        return response()->json($result);
    }

    public function getByParameters(Request $request)
    {
        $result = $this->lineUser->findUsers(
            $request->value,
            $request->start,
            $request->length,
            $request->order,
            $request->orderby
        );

        foreach ($result['data'] as $k => $r){
            if($r->block == 0){
                $result['data'][$k]['btn_block'] = '<button class="btn btn-sm btn-primary btn-block btn-warning" style="width: 94px">ブロック</button>';
            }else{
                $result['data'][$k]['btn_block'] = '<button class="btn btn-sm btn-primary btn-block btn-success" style="width: 94px">ブロック解除</button>';
            }

            $lineBotAccount = $this->lineBotAccount->find($r->line_bot_id);
            $result['data'][$k]['linebot_channel_name'] = $lineBotAccount->linebot_channel_name;
        }

        return response()->json($result);
    }

    public function getProfile(Request $request)
    {
        return $this->lineService->getProfile($request->id);
    }

    public function block(Request $request)
    {
        $data = $request->all();
        $block_new = $data['block'] == 1 ? 0 : 1;

        try {
            $result = $this->lineUser->update([
                "block" =>$block_new
            ], $data['id']);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        try {
            $this->lineUser->delete($request->id);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function add(Request $request)
    {
        $file_name_dir = public_path('/uploads/LineMail/' . $request->file_name);

        //open file csv with mode read
        if (($handle = fopen($file_name_dir, "r")) !== FALSE) {
            $i = 0;
            $rowCsv = [];

            //declare array result to check validate
            $resultHeaderInvalid = [];
            $resultAllNotNull = [];
            $resultUsernameNotExist = [];
            $resultEmailNull = [];
            $resultUsernameNull = [];
            $resultOk = [];

            //handle get data from file csv
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $i++;
                //push data into array row
                $rowCsv[$i] = $data;
                //if this is row first of file
                if ($i === 1) {
                    //check header file with 2 title column is メール & ユーザー名
                    if (trim($rowCsv[1][0]) !== 'メール'
                        || trim($rowCsv[1][1]) !== 'ユーザー名'
                    ) {
                        $resultHeaderInvalid[] = $i;
                    }
                } else {

                    //check column username is not exist
                    if (isset($data[0])
                        && !isset($data[1])
                    ){
                        $resultUsernameNotExist[] = $i;
                    }

                    //check column email or username is not null
                    if ( (
                            isset($data[0])
                            && trim($data[0]) !== ''
                        ) || (
                            isset($data[1])
                            && trim($data[1]) !== ''
                        )
                    ) {
                        $resultAllNotNull[] = $i;
                    }

                    //check column email is null
                    // & column username is not null
                    if (isset($data[0])
                        && trim($data[0]) === ''
                        && isset($data[1])
                        && trim($data[1]) !== ''
                    ) {
                        $resultEmailNull[] = $i;
                    }

                    //check column email is not null
                    // & column username is null
                    if (isset($data[0])
                        && trim($data[0]) !== ''
                        && isset($data[1])
                        && trim($data[1]) === ''
                    ) {
                        $resultUsernameNull[] = $i;
                    }

                    //check column email & username is valid
                    if (isset($data[0])
                        && trim($data[0]) !== ''
                        && filter_var($data[0], FILTER_VALIDATE_EMAIL)
                        && isset($data[1])
                        && trim($data[1]) !== ''
                    ) {
                        $resultOk[$i]['email'] = $data[0];
                        $resultOk[$i]['username'] = $data[1];
                    } else {
                        continue;
                    }
                }
            }
        }

        //close handle file
        fclose($handle);

        //delete all files has uploaded in folder LineMail
        array_map('unlink', glob("uploads/LineMail/*"));

        /* check if happen 1 in errors after:
         * - header is invalid
         * - all row is null
         * - username is not exist
         * - email is null
         * - username is null
         */
        if (count($resultHeaderInvalid) > 0
            || count($resultAllNotNull) === 0
            || count($resultUsernameNotExist) > 0
            || count($resultEmailNull) > 0
            || count($resultUsernameNull) > 0
        ){
            $messageArr = [];

            /* check if result no header
             * push into message array
             */
            if (count($resultHeaderInvalid) > 0) {
                $messageArr[] = 'no header at line 1,';
            }

            /* check if result no valid data
             * push into message array
             */
            if (count($resultAllNotNull) === 0) {
                $messageArr[] = 'no valid data,';
            }

            /* check if result username is not exist
             * push into message array
             */
            if (count($resultUsernameNotExist) > 0) {
                $rowsError = implode(", line ", $resultUsernameNotExist);
                $messageArr[] = 'not exist username column at line ' . $rowsError . ',';
            }

            /* check if result email is null
             * push into message array
             */
            if (count($resultEmailNull) > 0) {
                $rowsError = implode(", line ", $resultEmailNull);
                $messageArr[] = 'an empty email column at line ' . $rowsError . ',';
            }

            /* check if result username is null
             * push into message array
             */
            if (count($resultUsernameNull) > 0) {
                $rowsError = implode(", line ", $resultUsernameNull);
                $messageArr[] = 'an empty username column at line ' . $rowsError . ',';
            }

            //message show
            $message = "This csv file has: \n"
                . implode("\n", $messageArr) . "\n"
                . "please check it.";

            return $this->responseJson($message);

        } else { //result ok then process send mail

            //get all data from table emailImport
            $emailImport = $this->emailsImport
                ->all()
                ->toArray();
            $emailImportData = count($emailImport);

            //if data of table emailImport > 0
            //=> stop handling & return message
            if ($emailImportData > 0){
                $message = 'The process of sending mail of csv file before is not yet finished. Please wait.';
                return $this->responseJson($message);

            } else { //conduct processing send mail
                
                // get line bot id
                $lineBotId = $request->line_bot_id;
                
                //save result ok into table emailsImport
                foreach ($resultOk as $row) {
                    $this->emailsImport->create([
                        "email" =>$row['email'],
                        "username" =>$row['username'],
                        'line_bot_account_id' => $lineBotId,
                    ]);
                }

                //return message
                $message = 'The process of sending mail successfully !';
                return $this->responseJson($message);
            }
        }
    }

    private function responseJson($message)
    {
        return response()->json([
            'error' => true,
            'message' => $message
        ]);
    }

    public function upload(Request $request)
    {
        //get name of file upload
        $file_name = $request->request->get('file_emails')['filename'];

        //declare parts of file name
        $file_parts = pathinfo($file_name);

        //if extension of file is not csv
        // => return error is true
        if ($file_parts['extension'] !== 'csv'){
            return response()->json([
                'error'=> true
            ]);
        } else {
            //get value of file upload
            $value = $request->request->get('file_emails')['value'];

            //if no directory LineMail, then make it
            if (!is_dir('uploads/LineMail')) {
                mkdir('uploads/LineMail');
            }

            //put contents file to file in folder upload on project
            file_put_contents(
                'uploads/LineMail/'.$file_name,
                base64_decode($value)
            );

            //return file name & error is false
            return response()->json([
                'file_name' => $file_name,
                'error' => false
            ]);
        }
    }
}
