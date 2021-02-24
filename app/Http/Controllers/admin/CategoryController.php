<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class CategoryController extends AdminAuthController{
    public function list(Request $request){
		$objs = $this->category->search_all_records(array('sort_pos' => 'asc', 'where' => array('owner_id' => $this->super_admin_id)));
		return $this->returnSuccess($objs);
    }
    public function create(Request $request){
		$rules = [
			'name' => 'required|max:255',
		];
		$messages = [
			'name.required'   => __('Bạn chưa nhập tên Danh mục!'),
            'name.max'        => __('Tên Danh mục không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
        if(!empty($request->parent_id) && $request->parent_id !== 0){
            $parent_obj = $this->category->search_all_records(array('where' => array('parent_id' => $parent_id, 'owner_id' => $this->super_admin_id)));
			if(empty($parent_obj)){
				$error_data = array('parent_id' => array(__('Danh mục cha không tồn tại!')));
				return $this->returnError('Invalid Data', $error_data);
            }
        }
		
		$data = array(
			'owner_id' => $this->super_admin_id,
			'name' => $request->name,
			'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
			'position' => !empty($request->position) ? $request->position : 0,
		);
		$insert_id = $this->category->insert_record($data);
		return $this->returnSuccess($insert_id);
	}
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'name' => 'required|max:255',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'name.required'   => __('Bạn chưa nhập tên Danh mục!'),
            'name.max'        => __('Tên Danh mục không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
        if(!empty($request->parent_id) && $request->parent_id !== 0){
            $parent_obj = $this->category->search_all_records(array('where' => array('parent_id' => $parent_id, 'owner_id' => $this->super_admin_id)));
			if(empty($parent_obj)){
				$error_data = array('parent_id' => array(__('Danh mục cha không tồn tại!')));
				return $this->returnError('Invalid Data', $error_data);
            }
        }
		
		$data = array(
			'name' => $request->name,
			'parent_id' => !empty($request->parent_id) ? $request->parent_id : 0,
			'position' => !empty($request->position) ? $request->position : 0,
		);
		$update_data = $this->category->update_record($request->id, $data, array('where' => array('owner_id' => $this->super_admin_id)));
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
        
		$delete_data = $this->category->delete_record($request->id, array('where' => array('owner_id' => $this->super_admin_id)));
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
        
		$obj = $this->category->search_record_by_id_object($request->id, array('where' => array('owner_id' => $this->super_admin_id)));
		return $this->returnSuccess($obj);
	}
}