<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class UserController extends AdminAuthController{
    public function list(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->user->search_record_paging(array('sort_id' => 'desc'), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->user->count_all_records();
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function create(Request $request){
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
		return $this->returnSuccess($insert_id);
	}
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'role' => 'required|min:1|max:2',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'role.required' => __('Bạn cần chọn loại người dùng'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$data = array(
			'role' => $request->role,
		);
		if(!empty($request->full_name)){
			$data['full_name'] = $request->full_name;
		}
		if(!empty($request->address)){
			$data['address'] = $request->address;
		}
		if(!empty($request->phone)){
			$data['phone'] = $request->phone;
		}
		
		$update_data = $this->user->update_record($request->id, $data);
		return $this->returnSuccess($update_data);
	}
	public function delete(Request $request){
		$rules = [
			'id' => 'required',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
        
		$delete_data = $this->user->delete_record($request->id);
		return $this->returnSuccess($delete_data);
	}
	public function getInfo(Request $request){
		$rules = [
			'id' => 'required',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$obj = $this->user->search_record_by_id_object($request->id);
		return $this->returnSuccess($obj);
	}
}