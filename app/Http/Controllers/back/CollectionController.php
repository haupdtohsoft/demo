<?php

namespace App\Http\Controllers\Back;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BackAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class CollectionController extends BackAuthController{
    public function list(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->collection->search_record_paging(array('sort_id' => 'desc', 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->collection->count_all_records(array('where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function search(Request $request){
		$rules = [
			'name' => 'required|max:255',
		];
		$messages = [
			'name.required'   => __('Bạn chưa nhập tên Bộ sưu tập!'),
            'name.max'        => __('Tên Bộ sưu tập không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->collection->search_record_paging(array('sort_id' => 'desc', 'select' => array('id','name'), 'fulltext' => array('name' => $request->name), 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->collection->count_all_records(array('fulltext' => array('name' => $request->name), 'where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function create(Request $request){
		$rules = [
			'category_id' => 'required',
			'name' => 'required|max:255',
			'exams' => 'required',
		];
		$messages = [
			'category_id.required'   => __('Bạn chưa chọn Danh mục!'),
            'name.required'   => __('Bạn chưa nhập tên Bộ sưu tập!'),
            'name.max'        => __('Tên Bộ sưu tập không được vượt quá 255 ký tự!'),
			'exams.required'   => __('Bạn chưa nhập Bài thi!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$exams = CommonHelper::my_json_decode($request->exams);
		if(empty($exams) || !is_array($exams) || count($exams) <= 0){
			$error_data = array('exams' => array(__('Danh sách đề thi bị sai hoặc chưa điền!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$category_obj = $this->category->search_record_by_id_object($request->category_id, array('where' => array('owner_id' => $this->current_user->id)));
		if(empty($category_obj)){
			$error_data = array('category_id' => array(__('Danh mục không tồn tại!')));
			return $this->returnError('Invalid Data', $error_data);
		}
        
		$data = array(
			'owner_id' => $this->current_user->id,
			'name' => $request->name,
			'category_id' => $request->category_id,
			'exams' => $request->exams,
		);
		if(!empty($request->tag)){
			$data['tag'] = $request->tag;
		}
		if(!empty($request->description)){
			$data['description'] = $request->description;
		}
		if(!empty($request->status)){
			$data['status'] = $request->status;
		}
		if(!empty($request->price)){
			$data['price'] = $request->price;
		}
		if(!empty($request->is_private)){
			$data['is_private'] = $request->is_private;
		}
		if(!empty($request->password)){
			$data['password'] = $request->password;
		}
		
		$insert_id = $this->collection->insert_record($data);
		return $this->returnSuccess($insert_id);
	}
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'category_id' => 'required',
			'name' => 'required|max:255',
			'exams' => 'required',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'category_id.required'   => __('Bạn chưa chọn Danh mục!'),
            'name.required'   => __('Bạn chưa nhập tên Bộ sưu tập!'),
            'name.max'        => __('Tên Bộ sưu tập không được vượt quá 255 ký tự!'),
			'exams.required'   => __('Bạn chưa nhập Bài thi!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$exams = CommonHelper::my_json_decode($request->exams);
		if(empty($exams) || !is_array($exams) || count($exams) <= 0){
			$error_data = array('exams' => array(__('Danh sách đề thi bị sai hoặc chưa điền!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$category_obj = $this->category->search_record_by_id_object($request->category_id, array('where' => array('owner_id' => $this->current_user->id)));
		if(empty($category_obj)){
			$error_data = array('category_id' => array(__('Danh mục không tồn tại!')));
			return $this->returnError('Invalid Data', $error_data);
		}
        
		$data = array(
			'owner_id' => $this->current_user->id,
			'name' => $request->name,
			'category_id' => $request->category_id,
			'exams' => $request->exams,
		);
		if(!empty($request->tag)){
			$data['tag'] = $request->tag;
		}
		if(!empty($request->description)){
			$data['description'] = $request->description;
		}
		if(!empty($request->status)){
			$data['status'] = $request->status;
		}
		if(!empty($request->price)){
			$data['price'] = $request->price;
		}
		if(!empty($request->is_private)){
			$data['is_private'] = $request->is_private;
		}
		if(!empty($request->password)){
			$data['password'] = $request->password;
		}
		
		$update_data = $this->collection->update_record($request->id, $data, array('where' => array('owner_id' => $this->current_user->id)));
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
        
		$delete_data = $this->collection->delete_record($request->id, array('where' => array('owner_id' => $this->current_user->id)));
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
        
		$obj = $this->collection->search_record_by_id_object($request->id, array('where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess($obj);
	}
}