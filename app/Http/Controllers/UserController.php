<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use File;
use Mail;
use App\User;
use Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller {

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     * resources\lang\en\validation.php for custom error message
     */
    function __construct(User $User) {
        $this->UserModel = $User;
    }

    protected function validateUserSignUp(array $data) {
        $validation = Validator::make($data, [
                    'username' => 'required|max:50|regex:/^[a-zA-Z0-9][0-9a-zA-Z\\s]+$/',
                    'email' => 'required|email|max:255|unique:userpanel,emailid|regex:/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/',
                    'password' => 'required|min:5|confirmed|regex:/^[a-zA-Z0-9_@#!$%]*$/',
                    'password_confirmation' => 'required|min:5',
                    'mobile_number' => 'required|max:10|min:10'
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors();
            return $errors;
            //return $errors->toJson();
        } else {
            return false; // no form error
        }
    }

    /**
     * Create a new user after a valid registration.
     * @param  array  $data
     * @return User
     */
    private function create(array $data) {
        return User::create([
                    'username' => $data['username'],
                    'emailid' => $data['email'],
                    'password' => $data['password'],
                    'mobile_no' => $data['mobile_number'],
                    'user_type' => config('constant.COMMON_USER'),
                    'display_name' => $data['username'],
                    'date' => Carbon::now(),
                    'status' => config('constant.UNREGISTERED'),
                    'pics' => '',
                    'is_social' => config('constant.DEFAULT_SOCIAL_FLAG'),
                    'facebookId' => '',
                    'googleId' => '',
                    'location' => '',
                    'tokenId' => md5(str_random(20))
        ]);
    }

    /**
     * Create a new user insertion.
     * _isError sets true for any form error otherwise false
     * @param  array  $data
     * @return User
     */
    public function signUp(Request $request) {
        try {
            $posted = $request->only('username', 'email', 'password', 'mobile_number', 'password_confirmation');
            $_isError = $this->validateUserSignUp($posted);

            if (!$_isError) {
                $inserted_data = $this->create($posted);
                $html = File::get(public_path() . '/mail_templates/html/sign_up.html');
                $find = ['{{name}}', '{{api_url}}'];
                $replace = [$posted['username'], $_SERVER['HTTP_HOST'].'/user/activation/'.$inserted_data['tokenId']];
                $mailBody = str_replace($find, $replace, $html);
               
                Mail::send(array(), array(), function ($message) use ($mailBody, $posted) {
                    $message->to($posted['email'])
                            ->subject('Vivek Bindra::Signup')
                            ->setBody($mailBody, 'text/html');
                });
                        
                if (count(Mail::failures()) == 0) {
                    return response()->json(['data' => $inserted_data, 'status' => 'success', 'message' => 'user created', 'code' => 200]);
                } else {
                    return response()->json(['data' => '', 'status' => 'failed', 'message' => 'check mail settings. No user created', 'code' => 421]);
                }
            } else {
                return response()->json(['data' => '', 'status' => 'failed', 'message' => $_isError, 'code' => 409]);
            }
        } catch (\Exception $e) {
            return response()->json(['data' => '', 'status' => 'failed', 'message' => $e->getMessage(), 'code' => 421]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function userActivation($tokenId = null) {
        if($tokenId){
            $response = User::where('tokenId', '=', $tokenId)->first();
            if(!empty($response)){
                User::where('tokenId', '=', $tokenId)->update(array('tokenId' => '','status'=>config('constant.REGISTERED')));
                return response()->json(['data' => '', 'status' => 'success', 'message' => 'user registered successfully', 'code' => 200]);
            } 
        } 
        return response()->json(['data' => '', 'status' => 'failed', 'message' => 'invalid request', 'code' => 400]); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
