<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class ReportController extends AdminAuthController{
    public function list(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->report->search_record_paging(array('sort_id' => 'desc'), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->report->count_all_records();
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function create(Request $request){
		$rules = [
			'question_id' => 'required',
			'exam_id' => 'required',
			'description' => 'required|max:255',
		];
		$messages = [
			'question_id.required'   => __('Bạn chưa chọn Câu hỏi!'),
			'exam_id.required'   	 => __('Bạn chưa chọn Bài thi!'),
			'description.required'   => __('Bạn chưa nhập Miêu tả!'),
            'description.max'        => __('Miêu tả không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$data = array(
			'owner_id' => $this->current_user->id,
			'question_id' => $request->question_id,
			'exam_id' => $request->exam_id,
			'description' => $request->description,
		);
		
		$insert_id = $this->report->insert_record($data);
		return $this->returnSuccess($insert_id);
    }
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'question_id' => 'required',
			'exam_id' => 'required',
			'description' => 'required|max:255',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'question_id.required'   => __('Bạn chưa chọn Câu hỏi!'),
			'exam_id.required'   	 => __('Bạn chưa chọn Bài thi!'),
			'description.required'   => __('Bạn chưa nhập Miêu tả!'),
            'description.max'        => __('Miêu tả không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$data = array(
			'question_id' => $request->question_id,
			'exam_id' => $request->exam_id,
			'description' => $request->description,
		);
		
		$update_data = $this->report->update_record($request->id, $data);
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
	
		$delete_data = $this->report->delete_record($request->id);
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
        
		if(!empty($this->current_user) && $this->current_user->id == $this->super_admin_id){
			$obj = $this->report->search_record_by_id_object($request->id);
			return $this->returnSuccess($obj);
		}else{
			return $this->returnAuthenError();
		}
    }
}