<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class ReportController extends FrontAuthController{
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
		
		if(!empty($this->current_user)){
			$insert_id = $this->report->insert_record($data);
			return $this->returnSuccess($insert_id);
		}else{
			return $this->returnAuthenError();
		}
    }
}