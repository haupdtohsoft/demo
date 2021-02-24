<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class CollectionController extends FrontAuthController{
    public function listByCategory(Request $request){
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
        
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->collection->search_record_paging(array('sort_id' => 'desc', 'where' => array('category_id' => $request->id, 'is_private' => 0)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->collection->count_all_records(array('where' => array('category_id' => $request->id, 'is_private' => 0, 'status' => 1)));
		return $this->returnSuccess(array('objs' => CommonHelper::removePassword($objs), 'total' => $total));
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
        
		$obj = $this->collection->search_record_by_id_object($request->id);
		if(!empty($obj) && !empty($obj->password)){
			if($obj->password == $request->password){
				return $this->returnSuccess($obj);
			}else{
				$error_data = array('password' => array(__('Mật khẩu bạn nhập bị sai!')));
				return $this->returnError('Invalid Data', $error_data);
			}
		}else{
			return $this->returnSuccess($obj);
		}
	}
}