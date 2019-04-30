<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * login action
     */
    public function login(Request $request) {
        //validate user input
        $request->validate([
            'email' => ['required'],
            'password' => ['required']
        ]);
        //get user input
        $data = $request->only('email','password');
        //check for the email
        $exist = User::where('email',$data['email'])->first();
        if(!$exist) {
            return response()->json([
                "data" => "Invalid credentials"
            ],Response::HTTP_UNAUTHORIZED);
        }
        if(!Hash::check($data['password'],$exist->password)) {
            return response()->json([
                "data" => "Invalid credentials"
            ],Response::HTTP_UNAUTHORIZED);
        }
        //create userTokenn
        $userToken = $exist->createToken('Personal Access Token');
        $token = $userToken->token;
        $token->save();

        return response()->json([
            "data" => [
                "user" => $exist,
                "access_token" => $userToken->accessToken
            ]
            ],Response::HTTP_ACCEPTED);
    }
}
