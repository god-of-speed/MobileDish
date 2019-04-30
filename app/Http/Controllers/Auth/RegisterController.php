<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Service\UserService;
use App\Http\Controllers\Controller;
use App\Service\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required','string','unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * register action
     */
    public function register(Request $request,UserService $userService,NotificationService $notify) {
        //get a data
        $data = $request->only("name","email","username","password");
        //validate the info
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required','string','unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        
        //check if emai already exist
        $alreadyExist = User::where('email',$data['email'])->first();
        if($alreadyExist) {
            return response()->json("Email aready exist",Response::HTTP_CONFLICT);
        }
        //create user
        $user = $this->create($data);
        //create an access token
        $userToken = $user->createToken('Personal Access Token');
        $token = $userToken->token;
        $token->save();

        //create wallet
        $userWallet = $userService->createWallet($user);
        $notify = $notify->createNotification($user->id,'Welcome to MobileDish, we hope you get served right.','/user/index?user='.$user->id);

        return response()->json([
            'access_token' => $userToken->accessToken,
            'user' => $user
        ],Response::HTTP_CREATED);
    }
}
