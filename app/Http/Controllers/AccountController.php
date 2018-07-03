<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Illuminate\Container\Container as Application;
use App\Repository\Contracts\AccountInterface as AccountInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AccountController extends Controller
{
    //
    protected $account;
    protected $app;

    public function __construct(AccountInterface $account)
    {
        $this->account = $account;
    }

    public function index()
    {
   
        return $this->account->all();
    }
    public function signin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);
        $credentials = $request->only('username', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid Credentials!'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token!'
            ], 500);
        }
        return response()->json([
            'token' => $token
        ], 200);
    }
}
