<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class FavoriteController extends FrontAuthController{
	public function create(Request $request){
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
		
		if(!empty($this->current_user)){
			$obj = $this->favorite->search_record_by_id_object($request->id, array('where' => array('exam_id' => $request->id, 'owner_id' => $this->current_user->id)));
			if(empty($obj)){
				$exam_obj = $this->exam->search_record_by_id_object($request->id);
				if(!empty($exam_obj)){
					$this->exam->update_record($exam_obj->id, array('total_save' => $exam_obj->total_save + 1));
					$data = array(
						'owner_id' => $this->current_user->id,
						'exam_id' => $request->id,
					);
					$insert_id = $this->favorite->insert_record($data);
					return $this->returnSuccess($insert_id);
				}else{
					return $this->returnSuccess();
				}
			}else{
				return $this->returnSuccess($obj->id);
			}
		}else{
			return $this->returnAuthenError();
		}
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
        
		if(!empty($this->current_user)){
			$exam_obj = $this->exam->search_record_by_id_object($request->id);
			if(!empty($exam_obj)){
				$this->exam->update_record($exam_obj->id, array('total_save' => $exam_obj->total_save - 1));
				$delete_data = $this->favorite->delete_record($request->id, array('where' => array('owner_id' => $this->current_user->id)));
				return $this->returnSuccess($delete_data);
			}else{
				return $this->returnSuccess();
			}
		}else{
			return $this->returnAuthenError();
		}
	}
}