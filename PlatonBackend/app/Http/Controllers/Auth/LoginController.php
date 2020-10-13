<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\User;
use App\UserRole;
use Lang;
use DB;
use Exception;

class LoginController extends Controller
{
    

     /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="storeProject",
     *      tags={"Login a User"},
     *      summary="Login User",
     *      description="Returns User data",
     *      @OA\RequestBody(),
	 *      @OA\Parameter(
     *           name="email", in="query",required=true, @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *           name="password", in="query",required=true, @OA\Schema(type="string"),
     *      ),
	 *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *      ),
     * )
     */

    public function login(Request $request)
    {

        try {
            $validator = Validator::make(
                $request->all(), [
                'phone_no' => 'required | phone_no',
                'password' => 'required',
                ]
            );

            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                    throw new Exception($messages[0], 1);
                }
            }

            $user = User::where('phone_no', strtolower($request->phone_no))->first();

            if (empty($user)) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => Lang::get('messages.didnt_recognize_phone_no'),
                        'status_code' => 400,
                    ], 200
                );
            }

            

            if (!Hash::check($request->password,$user->password)) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => Lang::get('messages.password_wrong'),
                        'status_code' => 400,
                    ], 200
                );
            }
 
 
            $req = Request::create(
                '/oauth/token',
                'POST',
                [
                    'grant_type' => 'password',
                    'client_id' => '2',
                    'client_secret' => config('auth.passport.password_client_secret'),
                    'phone_no' => strtolower($request->phone_no),
                    'password' => $request->password,
                ]
            );
          
            $res = app()->handle($req);
           
            $auth = json_decode($res->getContent());
            if (isset($auth->error)) {
                throw new Exception('Authentication Failed.', 1);
            }
 
           

            $authorization_token = $auth->token_type.' '.$auth->access_token;

            $user->authorization_token = $authorization_token;
            
            return $this->sendResponse($user,"Login Successfully"); 
           
        } catch (RequestException $gex) {
            if ($gex->getCode() == 401) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Incorrect Username or Password.',
                        'status_code' => 401,
                    ], 401
                );
            } else {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $gex->getMessage(),
                        'status_code' => 400,
                    ], 400
                );
            }
        } catch (\Exception $ex) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $ex->getMessage(),
                    'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
                    'status_code' => 400,
                ], 400
            );
        }
    }

    public function error_message(){
        return  response()->json([
                        'status' => 'unauthenticated',
                        'message' => 'Invalid Authentication token',
                        'status_code' => 401
                    ], 401);
       
    }
    
}