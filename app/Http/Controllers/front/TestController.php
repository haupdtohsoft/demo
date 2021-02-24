<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class TestController extends FrontAuthController{
    public function listByExam(Request $request){
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
		$objs = $this->test->search_record_paging(array('sort_id' => 'desc', 'where' => array('exam_id' => $request->id, 'status' => 1)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->test->count_all_records(array('where' => array('exam_id' => $request->id, 'status' => 1)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function create(Request $request){
		$rules = [
			'exam_id' => 'required',
			'answers' => 'required',
			'status' => 'required',
		];
		$messages = [
			'exam_id.required'   => __('Bạn chưa chọn Bài thi!'),
            'answers.required'   => __('Bạn chưa điền câu trả lời!'),
			'status.required'   => __('Bạn chưa điền trạng thái bài test!'),
        ];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		$exam_obj = $this->exam->search_record_by_id_object($request->exam_id);
		if(empty($exam_obj)){
			$error_data = array('exam_id' => array(__('Bài thi không tồn tại!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$delay_test_time = config('app.delay_test_time');
		if(!empty($obj->end_time) && (strtotime(date('Y-m-d H:i:s')) - $delay_test_time > strtotime($obj->end_time))){
			$error_data = array('end_time' => array(__('Bài thi đã kết thúc!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$answers = CommonHelper::my_json_decode($request->answers);
		if(!is_array($answers)){
			$error_data = array('answers' => array(__('Câu trả lời phải là mảng!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$result_obj = CommonHelper::validUserAnswers($answers, CommonHelper::my_json_decode($exam_obj->questions));
		if($result_obj === FALSE){
			$error_data = array('answers' => array(__('Câu trả lời sai định dạng!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		if(!empty($this->current_user)){
			$data = array(
				'owner_id' => $this->current_user->id,
				'exam_id' => $exam_obj->id,
				'owner_exam_id' => $exam_obj->owner_id,
				'answers' => $request->answers,
				'status' => $request->status,
			);
			if(!empty($request->start_time)){
				$data['start_time'] = $request->start_time;
			}
			if(!empty($request->end_time)){
				$data['end_time'] = $request->end_time;
			}
			if($request->status == 1){
				$data['total_correct'] = $result_obj->correct;
				$data['total_incorrect'] = $result_obj->incorrect;
				$data['total_not_answer'] = $result_obj->unanswer;
				$data['got_mark'] = round(($exam_obj->total_mark / $exam_obj->total_question) *$result_obj->correct);
			}
			
			$insert_id = $this->test->insert_record($data);
			return $this->returnSuccess($insert_id);
		}else{
			return $this->returnAuthenError();
		}
    }
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'exam_id' => 'required',
			'answers' => 'required',
			'status' => 'required',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'exam_id.required'   => __('Bạn chưa chọn Bài thi!'),
            'answers.required'   => __('Bạn chưa điền câu trả lời!'),
			'status.required'   => __('Bạn chưa điền trạng thái bài test!'),
       ];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		$exam_obj = $this->exam->search_record_by_id_object($request->exam_id);
		if(empty($exam_obj)){
			$error_data = array('exam_id' => array(__('Bài thi không tồn tại!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
        $delay_test_time = config('app.delay_test_time');
		if(!empty($obj->end_time) && (strtotime(date('Y-m-d H:i:s')) - $delay_test_time > strtotime($obj->end_time))){
			$error_data = array('end_time' => array(__('Bài thi đã kết thúc!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$answers = CommonHelper::my_json_decode($request->answers);
		if(!is_array($answers)){
			$error_data = array('answers' => array(__('Câu trả lời phải là mảng!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		$result_obj = CommonHelper::validUserAnswers($answers, CommonHelper::my_json_decode($exam_obj->questions));
		if($result_obj === FALSE){
			$error_data = array('answers' => array(__('Câu trả lời sai định dạng!')));
			return $this->returnError('Invalid Data', $error_data);
		}
		
		if(!empty($this->current_user)){
			$data = array(
				'owner_id' => $this->current_user->id,
				'exam_id' => $exam_obj->id,
				'owner_exam_id' => $exam_obj->owner_id,
				'answers' => $request->answers,
				'status' => $request->status,
			);
			
			if(!empty($request->start_time)){
				$data['start_time'] = $request->start_time;
			}
			if(!empty($request->end_time)){
				$data['end_time'] = $request->end_time;
			}
			
			if($request->status == 1){
				$data['total_correct'] = $result_obj->correct;
				$data['total_incorrect'] = $result_obj->incorrect;
				$data['total_not_answer'] = $result_obj->unanswer;
				$data['got_mark'] = round(($exam_obj->total_mark / $exam_obj->total_question) *$result_obj->correct);
			}
			
			$update_data = $this->test->update_record($request->id, $data, array('where' => array('owner_id' => $this->current_user->id, 'status' => 0)));
			return $this->returnSuccess($update_data);
		}else{
			return $this->returnAuthenError();
		}
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
        
		$obj = $this->test->search_record_by_id_object($request->id);
		return $this->returnSuccess($obj);
	}
}