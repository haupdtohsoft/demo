<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class CategoryController extends FrontAuthController
{
    public function list(Request $request){
		$objs = $this->category->search_all_records(array('sort_pos' => 'asc', 'where' => array('owner_id' => $this->super_admin_id)));
		return $this->returnSuccess($objs);
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
        
		$obj = $this->category->search_record_by_id_object($request->id, array('where' => array('owner_id' => $this->super_admin_id)));
		return $this->returnSuccess($obj);
	}
}