<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

use App\User;
use App\Entities\Asset;
use App\Entities\Category;
use App\Entities\Collection;
use App\Entities\Exam;
use App\Entities\Question;
use App\Entities\Tag;
use App\Entities\Test;
use App\Entities\Report;
use App\Entities\Favorite;
use App\Entities\Oldquestion;

use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller{
	public function __construct(){
		$this->laravel_time = $this->getTimeExecute();
		
		$this->item_per_page = config('app.item_per_page');
		$this->super_admin_id = config('app.super_admin_id');
		
		$this->asset = new Asset();
		$this->category = new Category();
		$this->collection = new Collection();
		$this->exam = new Exam();
		$this->question = new Question();
		$this->tag = new Tag();
		$this->test = new Test();
		$this->report = new Report();
		$this->favorite = new Favorite();
		$this->user = new User();
		$this->oldquestion = new Oldquestion();
		
		$this->payload_user = $this->getPayloadUser();
		$this->current_user = $this->getCurrentUser();
	}
	
	public function login(Request $request){
		$rules = [
			'email' => 'required|email',
			'password' => 'required',
		];
		$messages = [
			'email.required' => __('Bạn cần nhập Email'),
			'email.email' => __('Email sai định dạng'),
			'password.required' => __('Bạn cần nhập mật khẩu'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$obj = $this->user->search_all_records(array('first' => true, 'where' => array('email' => $request->email)));
		if(!empty($obj)){
			if(Hash::check($request->password, $obj->password)){
				$obj = $this->createUserWithToken($obj);
				return $this->returnSuccess($obj);
			}else{
				return $this->returnError('lang_cms_error_invalid_email_or_password');
			}
		}else{
			return $this->returnError('lang_cms_error_invalid_email_or_password');
		}
	}
	public function register(Request $request){
		$rules = [
			'email' => 'required|email',
			'password' => 'required',
			'role' => 'required|min:1|max:2',
		];
		$messages = [
			'email.required' => __('Bạn cần nhập Email'),
			'email.email' => __('Email sai định dạng'),
			'password.required' => __('Bạn cần nhập mật khẩu'),
			'role.required' => __('Bạn cần chọn loại người dùng'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$obj = $this->user->search_all_records(array('first' => true, 'where' => array('email' => $request->email)));
		if(!is_object($obj)){
			$data = array(
				'email' => $request->email,
				'password' => bcrypt($request->password),
				'updated_password' => strtotime(date('Y-m-d H:i:s')),
				'full_name' => !empty($request->full_name) ? $request->full_name : '',
				'address' => !empty($request->address) ? $request->address : '',
				'phone' => !empty($request->phone) ? $request->phone : '',
				'role' => $request->role,
			);
			$insert_id = $this->user->insert_record($data);
			$new_obj = $this->user->search_record_by_id_object($insert_id);
			$new_obj = $this->createUserWithToken($new_obj);
			return $this->returnSuccess($new_obj);
		}
		return $this->returnError('lang_cms_error_email_exists');
	}
	public function changePassword(Request $request){
		if(!empty($this->current_user)){
			if(!empty($request->new_password)){
				$obj = $this->user->search_all_records(array('first' => true, 'where' => array('email' => $this->current_user->email)));
				if(!empty($obj)){
					if(Hash::check($request->old_password, $obj->password)){
						$this->user->update_record($this->current_user->id, array(
							'password' => bcrypt($request->new_password),
							'updated_password' => strtotime(date('Y-m-d H:i:s'))
						));
						return $this->returnSuccess();
					}else{
						return $this->returnError('lang_cms_error_old_password_incorrect');
					}
				}else{
					return $this->returnError('lang_cms_error_invalid_email_or_password');
				}
			}else{
				return $this->returnError('lang_cms_error_invalid_data');
			}
		}else{
			return $this->returnAuthenError();
		}
	}
	public function forgotPassword(Request $request){
		$domain = $request->domain;
		$email = $request->email;
		$confirm_slug = $request->confirm_slug;
		$obj = $this->user->search_all_records(array('first' => true, 'where' => array('email' => $email)));
		if(is_object($obj)){
			$reset_code = md5(Hash::make(date('Y-m-d H:i:s'). rand(0, 1000000)));
			$this->user->update_record($obj->id, array('forgot_password_token' => $reset_code));
			$content_email = ['domain' => 'https://'. $domain, 'link' => 'https://'.$domain.$confirm_slug.'?email='.urlencode($email).'&token='.urlencode($reset_code)];
			CommonHelper::send_email($email, $content_email, '[Forgot Password: https://'.$domain.']', 'forgot_password');
			return $this->returnSuccess();
		}
		return $this->returnError('lang_cms_error_email_not_exists');
	}
	public function confirmForgotPassword(Request $request){
		if(!empty($request->email) && !empty($request->token) && !empty($request->password)){
			$obj = $this->user->search_all_records(array('first' => true, 'where' => array('email' => $request->email, 'forgot_password_token' => $request->token)));
			if(is_object($obj)){
				$this->user->update_record($obj->id, array(
					'password' => bcrypt($request->password),
					'forgot_password_token' => '',
					'updated_password' => strtotime(date('Y-m-d H:i:s'))
				));
				return $this->returnSuccess();
			}else{
				return $this->returnError('lang_cms_error_invalid_data');
			}
		}else{
			return $this->returnError('lang_cms_error_invalid_data');
		}
	}
	public function openid(Request $request){
		$firebase_auth = app('firebase.auth');
		$idTokenString = $request->access_token;
		try{
			$verifiedIdToken = $firebase_auth->verifyIdToken($idTokenString);
			$firebase_uid = $verifiedIdToken->getClaim('sub');
			$firebase_user = $firebase_auth->getUser($firebase_uid);
			if(isset($firebase_user->providerData) && isset($firebase_user->providerData[0])){
				$email = $firebase_user->providerData[0]->email;
				$obj = $this->user->search_all_records(array('first' => true, 'where' => array('email' => $email)));
				if(!empty($obj)){
					$this->user->update_record($obj->id, array(
						'full_name' => $firebase_user->providerData[0]->displayName,
						'avatar' => $firebase_user->providerData[0]->photoUrl,
						'client_id' => $firebase_user->providerData[0]->uid
					));
					$new_obj = $this->user->search_record_by_id_object($obj->id);
					$new_obj = $this->createUserWithToken($new_obj);
					return $this->returnSuccess($new_obj);
				}else{
					$password = CommonHelper::generate_random_string(8);
					$insert_id = $this->user->insert_record(array(
						'full_name' => $firebase_user->providerData[0]->displayName,
						'avatar' => $firebase_user->providerData[0]->photoUrl,
						'client_id' => $firebase_user->providerData[0]->uid,
						'email' => $email,
						'password' => bcrypt($password),
						'updated_password' => strtotime(date('Y-m-d H:i:s'))
					));
					$new_obj = $this->user->search_record_by_id_object($insert_id);
					$new_obj = $this->createUserWithToken($new_obj);
					return $this->returnSuccess($new_obj);
				}
			}else{
				return $this->returnError();
			}
		}catch (\InvalidArgumentException $e){
			return $this->returnError('The token could not be parsed: '.$e->getMessage());
		}catch (InvalidToken $e){
			return $this->returnError('The token is invalid: '.$e->getMessage());
		}
	}
	public function updateInfo(Request $request){
		if(!empty($this->current_user)){
			$update_datas = array();
			if(!empty($request->bio)){
				$update_datas['bio'] = $request->bio;
			}
			if(!empty($request->full_name)){
				$update_datas['full_name'] = $request->full_name;
			}
			if(!empty($request->birthday)){
				$update_datas['birthday'] = $request->birthday;
			}
			if(!empty($request->phone)){
				$update_datas['phone'] = $request->phone;
			}
			if(!empty($request->address)){
				$update_datas['address'] = $request->address;
			}
			if(!empty($request->avatar)) {
				$update_datas['avatar'] = $request->avatar;
			}
			if(count($update_datas) > 0){
				$this->user->update_record($this->current_user->id, $update_datas);
			}
			return $this->returnSuccess();
		}else{
			return $this->returnAuthenError();
		}
	}
	
	public function returnSuccess($data = array()){
		$data = (object)array('success' => 1, 'code' => 200, 'message' => 'successful', 'data' => $data, 'time' => $this->laravel_time, 'return_time' => $this->getTimeExecute());
		if(config('app.compress_json_response')){
			return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
		}else{
			return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		}
    }
	public function returnError($mess = '', $data = array()){
		$data = (object)array('success' => 0, 'code' => 400, 'message' => $mess, 'data' => $data, 'time' => $this->laravel_time, 'return_time' => $this->getTimeExecute());
		if(config('app.compress_json_response')){
			return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
		}else{
			return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		}
    }
	public function returnAuthenError($data = array()){
		if(config('app.compress_json_response')){
			return response()->json(['success' => 0, 'code' => 403, 'message' => 'lang_cms_error_not_authen', 'data' => $data, 'time' => $this->laravel_time, 'return_time' => $this->getTimeExecute()], 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
		}else{
			return response()->json(['success' => 0, 'code' => 403, 'message' => 'lang_cms_error_not_authen', 'data' => $data, 'time' => $this->laravel_time, 'return_time' => $this->getTimeExecute()], 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		}
    }
	public function returnDebug($data){
		 return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	}
	
	public function getCurrentUser(){
		$db_user = $this->user->search_record_by_id_object(1);
		return $db_user;
		
		if(!empty($this->payload_user) && !empty($this->payload_user->custom_obj)){
			$db_user = $this->user->search_record_by_id_object($this->payload_user->custom_obj->id);
			if($this->payload_user->custom_obj->updated_password != $db_user->updated_password){
				return null;
			}else{
				return $db_user;
			}
		}else{
			return null;
		}
    }
    public function createUserWithToken($user){
		if(isset($user->forgot_password_token)){
			unset($user->forgot_password_token);
		}
		if(isset($user->password)){
			unset($user->password);
		}
		$payload = JWTFactory::sub($user->id)->custom_obj($user)->make();
		$token = JWTAuth::encode($payload);
		$user->access_token = $token->get();
		return $user;
	}
	public function getPayloadUser(){
		try{
			$valid_token = JWTAuth::parseToken();
			if($valid_token){
				$token = JWTAuth::getToken();
				$tokenParts = explode(".", $token); 
				$tokenPayload = base64_decode($tokenParts[1]);
				$jwtPayload = json_decode($tokenPayload);
				return $jwtPayload;
			}else{
				return null;
			}
		}catch(JWTException $e) {
			return null;
		}catch(Exception $e){
			return null;
		}
	}
	public function getTimeExecute(){
		return round(microtime(true) * 1000) - round(LARAVEL_START * 1000);
	}
}