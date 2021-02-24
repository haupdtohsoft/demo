<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class ExamController extends FrontAuthController{
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
		$objs = $this->exam->search_record_paging(array('sort_id' => 'desc', 'where' => array('category_id' => $request->id, 'is_private' => 0, 'status' => 1)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('where' => array('category_id' => $request->id, 'is_private' => 0, 'status' => 1)));
		return $this->returnSuccess(array('objs' => CommonHelper::removePassword($objs), 'total' => $total));
	}
	public function listByCollection(Request $request){
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
		if(!empty($obj)){
			$exam_ids = CommonHelper::my_json_decode($obj->exams);
			if($exam_ids !== null){
				if(count($exam_ids) > 0){
					$objs = $this->exam->search_all_records(array('where_in' => array('id' => $exam_ids), 'where' => array('is_private' => 0, 'status' => 1)));
					return $this->returnSuccess(CommonHelper::removePassword($objs));
				}else{
					return $this->returnSuccess();
				}
			}else{
				return $this->returnError('lang_cms_error_invalid_data');
			}
		}else{
			return $this->returnError('lang_cms_error_invalid_data');
		}
	}
	public function listByUser(Request $request){
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
		$objs = $this->exam->search_record_paging(array('sort_id' => 'desc', 'where' => array('owner_id' => $request->id, 'is_private' => 0, 'status' => 1)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('where' => array('owner_id' => $request->id, 'is_private' => 0, 'status' => 1)));
		return $this->returnSuccess(array('objs' => CommonHelper::removePassword($objs), 'total' => $total));
	}
	public function topExam(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->exam->search_record_paging(array('sort_by' => array('user_tested' => 'desc'), 'where' => array('is_private' => 0, 'status' => 1)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('where' => array('is_private' => 0, 'status' => 1)));
		return $this->returnSuccess(array('objs' => CommonHelper::removePassword($objs), 'total' => $total));
	}
	public function newExam(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->exam->search_record_paging(array('sort_id' => 'desc', 'where' => array('is_private' => 0, 'status' => 1)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('where' => array('is_private' => 0, 'status' => 1)));
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
        
		$obj = $this->exam->search_record_by_id_object($request->id);
		if(!empty($obj)){
			if(!empty($obj->password)){
				if($obj->password == $request->password){
					if(!empty($obj->start_time) && strtotime(date('Y-m-d H:i:s')) < strtotime($obj->start_time)){
						$error_data = array('start_time' => array(__('Chưa tới thời gian được làm đề!')));
						return $this->returnError('Invalid Data', $error_data);
					}else{
						return $this->returnSuccess($obj);
					}
				}else{
					$error_data = array('password' => array(__('Mật khẩu bạn nhập bị sai!')));
					return $this->returnError('Invalid Data', $error_data);
				}
			}else{
				if(!empty($obj->start_time) && strtotime(date('Y-m-d H:i:s')) < strtotime($obj->start_time)){
					$error_data = array('start_time' => array(__('Chưa tới thời gian được làm đề!')));
					return $this->returnError('Invalid Data', $error_data);
				}else{
					return $this->returnSuccess($obj);
				}
			}
		}else{
			return $this->returnSuccess($obj);
		}
	}
}