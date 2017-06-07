<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Carbon\Carbon;
use App\User;

class AuthController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Authorization Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the Authentication and token creation,
      | By default, this controller uses
      | a simple trait to add these behaviors. Why don't you explore it?
      |
     */

    //use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    private $roleObj;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Request $request) {
        $this->roleObj->role == 1;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function authenticate(Request $request) {
        $credentials = $request->only('email', 'password');
dd($credentials);
       
        $checkCredentialAlgo = User::whereEmail($credentials['email'])->first();
        dd($checkCredentialAlgo);
                //->wherePassword(md5($credentials['password']))
                //->first();
        if ($checkCredentialAlgo['id']) {
            $password = bcrypt($credentials['password']);
            \App\Modules\User\Models\UserModel::where('id', '=', $checkCredentialAlgo['id'])->update(['password' => $password, 'password_hash' => '1']);
        }
        /*         * ******* Hash Ends here ************************************************************************************ */

        if (!isset($this->roleObj->role)) {
            $this->roleObj->role = 2;
        }
        $customClaims = ['role' => $this->roleObj->role];

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials, $customClaims)) {
                //return response()->json(['error' => 'invalid_credentials'], 401);
                return ['data' => ['token' => ''], 'status' => 'fail', 'message' => 'invalid_credentials', 'code' => 401];
            }
        } catch (JWTException $ex) {
            // something went wrong whilst attempting to encode the token
            return ['data' => ['token' => ''], 'status' => 'fail', 'message' => 'could_not_create_token', 'code' => 500];
            //return response()->json(['error' => 'could_not_create_token'], 500);
        }

        //User::insertTokens(['token' => $token,'created_on' => Carbon::now()->format('Y-m-d H:i:s')]); // saves tokens to DB

        call_user_func(array(\Config::get('auth.model'), 'insertTokens'), array('user_type' => $this->roleObj->role, 'token' => $token, 'token_issued_at' => Carbon::now()->format('Y-m-d H:i:s')));

        return ['data' => ['token' => 'Bearer ' . $token], 'status' => 'success', 'message' => '', 'code' => 200];
        //return response()->json(compact('token'))->setStatusCode(200);
    }

    /**
     * Returns the logged in user details based on token
     *
     * @param  array  $data
     * @return User
     */
    public function loggerDetail() {
        $payload = JWTAuth::parseToken()->getPayload();
        //prx($payload['role']);
        /* $token = JWTAuth::getToken();
          $user = $auth->toUser();
          prx($user = JWTAuth::toUser($token));
          return JWTAuth::parseToken()->toUser(); */

        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;
        $loggerAddress = null;
        if (isset($userId)) {
            if ($payload['role'] == 1) {
                $loggerDetails = \App\Modules\User\Models\AdminModel::find($userId);

                $total_order_new = OrderModel::where('order_status', '1')->count();
                $total_order_pending = OrderModel::where('order_status', '2')->count();

                $summary = array(
                    'total_order_new' => $total_order_new,
                    'total_order_pending' => $total_order_pending
                );
            } else if ($payload['role'] == 2) {
                $loggerDetails = \App\Modules\User\Models\UserModel::find($userId);
                $loggerAddress = \App\Modules\User\Models\UserAddressModel::where('user_id', $userId)->first();

                $total_order = OrderModel::where('customer_id', $userId)->count();
            }
        }
        $url = Config::get('app.url') . "/" . Config::get('app.profile_image_path');
        $profile_image_path = "";
        if ($loggerDetails['profile_image']) {
            $profile_image_path = $url . "/" . $loggerDetails['id'] . "/thumb/" . $loggerDetails['profile_image'];
        }
        return [
            'id' => $loggerDetails['id'],
            'role' => $payload['role'],
            'name' => $loggerDetails['name'],
            'email' => $loggerDetails['email'],
            'mobile_number' => $loggerDetails['mobile_number'],
            'profile_image' => $profile_image_path,
            'google_id' => $loggerDetails['google_id'],
            'fb_id' => $loggerDetails['fb_id'],
            'address' => $loggerAddress,
            'order_count' => $total_order,
            'summary' => $summary
        ];
    }

    function logout() {
        $token = JWTAuth::getToken();
        if ($token) {

            JWTAuth::invalidate($token);
            //JWTAuth::setToken($token)->invalidate();
            return ['data' => '', 'status' => 'success', 'message' => 'Token blacklisted', 'code' => 200];
        }
        return ['data' => '', 'status' => 'failed', 'message' => 'Token unavailable to blacklist', 'code' => 403];
    }

}
