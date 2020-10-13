<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserRole;
use App\UserDetail;
use Lang;
use Illuminate\Support\Facades\Validator;

use Exception;
use File;
use DB;
use Storage;
use Image;
use Carbon\Carbon;

class ProfileController extends Controller
{
	private $profile_picture_display_path;
    private $profile_picture_upload_path;


	public function __construct(){
		$this->profile_picture_display_path = config('app.app_root').config('app.img_path.profile_picture');
        $this->profile_picture_upload_path = storage_path('images/profile_images');
	}

    public function getProfile($id)
    {
        try {
            $resultSet = User::with('user_details')
            ->where('users.id', $id)
            ->first();

            if (is_null($resultSet)) {
            	return $this->sendError(array(),Lang::get('messages.user_not_found'));
        	}

            if($resultSet->user_details->profile_pic != null){
               $resultSet->user_details['image_url'] = $this->profile_picture_display_path.'/'.$resultSet->user_details->profile_pic;
            }else{
                $resultSet->user_details['image_url'] = null;
            }
            
        	$otherData['image_path'] = $this->profile_picture_display_path;

        	return $this->sendResponse($resultSet,Lang::get('messages.user_index_data'),$otherData);

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

     public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'adhar_card_no'=>'required',
                'category'=>'required'
                ]);

            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                    throw new Exception($messages[0], 1);
                }
            }
            $userscount = User::where([
                ['id', '=',  $request->id],
                ['user_name', '=',  strtolower($request->email)],
            ])->count();
            
            if ($userscount > 1) {
                throw new Exception('User already registered with same User Name.', 1);
            }
            DB::beginTransaction();

            //User Update 
            $userProfile = User::where('id',json_decode($request->id))->first();
            $userProfile->first_name = ucwords($request->first_name);
            $userProfile->last_name = ucwords($request->last_name);
            $userProfile->phone_no = $request->phone_no;
            $userProfile->save();
            


            //User Detail Update
             $userDetail = UserDetail::where('user_id',$userProfile->id)->firstOrFail();
             $profile_image = $request->file('myfile');
             // removing old file if available
            if (!empty($profile_image)) {
                File::delete($this->profile_picture_upload_path.$userDetail['profile_pic']);
            }

          
            $file_name = null;
            if ($profile_image) {
                $image = Image::make($profile_image)->resize('200','200');
                $file_name = time().'.'.$profile_image->getClientOriginalExtension();
                $destination_path = storage_path('images/profile_images/');
                $image->save($destination_path . $file_name);
               //Storage::disk('public')->put($file_name,file_get_contents($image->getRealPath()));
            }

            //User Detail Update
            $userDetail->first_name = strtolower($request->first_name);
            $userDetail->last_name = $request->last_name;
            $userDetail->adhar_card_no = json_decode($request->adhar_card_no);
            $userDetail->skill_set = json_decode($request->skill_set);
            $userDetail->category = $request->category;
            
            

            if($file_name != null){
                $userDetail->profile_pic =  $file_name;    
            }
            $userDetail->save();
           
            $profile =  User::with('user_details')
                ->where('users.id', $request->id)
                ->first();

            if($profile->user_details->profile_pic != null){
               $profile->user_details['image_url'] = $this->profile_picture_display_path.'/'.$profile->user_details->profile_pic;
            }else{
                $profile->user_details['image_url'] = null;
            }

            $otherData['image_path'] = $this->profile_picture_display_path;

           DB::commit();
          return $this->sendResponse($profile,Lang::get('messages.profile_update'),$otherData);
            
    } catch (\Exception $ex) {
         DB::rollback();

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
}
