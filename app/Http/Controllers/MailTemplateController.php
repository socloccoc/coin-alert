<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\MailTemplateInterface;

class MailTemplateController extends Controller
{
    protected $mailTemplate;
    public function __construct(MailTemplateInterface $mailTemplate)
    {
        $this->mailTemplate = $mailTemplate;
    }

    public function index(Request $request)
    {
        return response()->json($this->mailTemplate->all());
    }

    public function save(Request $request)
    {
        try {
            $this->mailTemplate->save(
                $request->title,
                $request->content,
                $request->type
            );
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }
}