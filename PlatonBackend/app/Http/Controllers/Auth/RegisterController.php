<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Lang;
use DB;
use Exception;
use App\Mail\ConfirmEmail;
use Carbon\Carbon;

class RegisterController extends Controller
{
	public function __construct()
    {
        $this->middleware('guest');
    }

	 /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="storeProject",
     *      tags={"Register a User"},
     *      summary="Register new User",
     *      description="Returns User data",
     *      @OA\RequestBody(),
     *      @OA\Parameter(
     *           name="phone_no", in="query",required=true, @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *           name="user_name", in="query",required=true, @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *           name="password", in="query",required=true, @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *           name="password_confirmation", in="query",required=true, @OA\Schema(type="string"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */


	 public function register(Request $request)
    {
   
    try{


            $validator = Validator::make($request->all(), [
                'phone_no' => ['required','string', 'max:255'],
                'user_name' => ['required', 'string', 'user_name', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                    throw new Exception($messages[0], 1);
                }
            }
            $userCount = User::where('user_name',  strtolower($request->user_name))->count();
            if ($userCount > 0) {
                throw new \Exception(Lang::get('messages.user_exist'), 1);
            }  

         DB::beginTransaction();

         $user = User::create(
                [
                    'phone_no' => $request->phone_no,
                    'user_name' => strtolower($request->user_name),
                    'password' => Hash::make($request->password),
                ]
            );

        DB::commit();

        return $this->sendResponse([],Lang::get('messages.user_register'));
    }catch(Exception $ex){

        DB::rollback();

            return response()->json(
                [
                    'status' => 'error',
                    'message' => $ex->getMessage(),
                    'error_details' => 'on line : '.$ex->getLine().' on file : '.$ex->getFile(),
                    'status_code' => 400,
                ], 201
            );
    }

   }
   
}
