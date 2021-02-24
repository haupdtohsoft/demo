<?php

namespace App\Http\Controllers\Back;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BackAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class ExamController extends BackAuthController{
    public function list(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->exam->search_record_paging(array('sort_id' => 'desc', 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function search(Request $request){
		$rules = [
			'name' => 'required|max:255',
		];
		$messages = [
			'name.required'   => __('Bạn chưa nhập tên Bài thi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->exam->search_record_paging(array('sort_id' => 'desc', 'fulltext' => array('name' => $request->name), 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('select' => array('id','content'), 'fulltext' => array('name' => $request->name), 'where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function create(Request $request){
		$rules = [
			'category_id' => 'required',
			'type' => 'required',
			'name' => 'required|max:255',
			'questions' => 'required',
			'total_mark' => 'required',
		];
		$messages = [
			'category_id.required'   => __('Bạn chưa chọn Danh mục!'),
            'type.required'   => __('Bạn chưa chọn Kiểu bài thi!'),
            'name.required'   => __('Bạn chưa nhập tên Bài thi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
			'questions.required'   => __('Bạn chưa nhập Câu hỏi!'),
			'total_mark.required'   => __('Bạn chưa nhập Thang điểm!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$questions = CommonHelper::validQuestions(CommonHelper::my_json_decode($request->questions));
		if($questions === FALSE){
			$error_data = array('questions' => array(__('Câu hỏi sai định dạng!')));
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
			'type' => $request->type,
			'questions' => CommonHelper::my_json_encode($questions),
			'total_mark' => $request->total_mark,
			'total_question' => $questions === null ? 0 : count($questions),
		);
		if(!empty($request->tag)){
			$data['tag'] = $request->tag;
		}
		if(!empty($request->description)){
			$data['description'] = $request->description;
		}
		if(!empty($request->show_answer)){
			$data['show_answer'] = $request->show_answer;
		}
		if(!empty($request->price_discount)){
			$data['price_discount'] = $request->price_discount;
		}
		if(!empty($request->status)){
			$data['status'] = $request->status;
		}
		if(!empty($request->price)){
			$data['price'] = $request->price;
		}
		if(!empty($request->time)){
			$data['time'] = $request->time;
		}
		if(!empty($request->is_private)){
			$data['is_private'] = $request->is_private;
		}
		if(!empty($request->pass_mark)){
			$data['pass_mark'] = $request->pass_mark;
		}
		if(!empty($request->start_time)){
			$data['start_time'] = $request->start_time;
		}
		if(!empty($request->end_time)){
			$data['end_time'] = $request->end_time;
		}
		if(!empty($request->password)){
			$data['password'] = $request->password;
		}
		
		$insert_id = $this->exam->insert_record($data);
		return $this->returnSuccess($insert_id);
	}
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'category_id' => 'required',
			'type' => 'required',
			'name' => 'required|max:255',
			'questions' => 'required',
			'total_mark' => 'required',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'category_id.required'   => __('Bạn chưa chọn Danh mục!'),
            'type.required'   => __('Bạn chưa chọn Kiểu bài thi!'),
            'name.required'   => __('Bạn chưa nhập tên Bài thi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
			'questions.required'   => __('Bạn chưa nhập Câu hỏi!'),
			'total_mark.required'   => __('Bạn chưa nhập Thang điểm!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$questions = CommonHelper::validQuestions(CommonHelper::my_json_decode($request->questions));
		if($questions === FALSE){
			$error_data = array('questions' => array(__('Câu hỏi sai định dạng!')));
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
			'type' => $request->type,
			'questions' => CommonHelper::my_json_encode($questions),
			'total_mark' => $request->total_mark,
			'total_question' => $questions === null ? 0 : count($questions),
		);
		if(!empty($request->tag)){
			$data['tag'] = $request->tag;
		}
		if(!empty($request->description)){
			$data['description'] = $request->description;
		}
		if(!empty($request->show_answer)){
			$data['show_answer'] = $request->show_answer;
		}
		if(!empty($request->price_discount)){
			$data['price_discount'] = $request->price_discount;
		}
		if(!empty($request->status)){
			$data['status'] = $request->status;
		}
		if(!empty($request->price)){
			$data['price'] = $request->price;
		}
		if(!empty($request->time)){
			$data['time'] = $request->time;
		}
		if(!empty($request->is_private)){
			$data['is_private'] = $request->is_private;
		}
		if(!empty($request->pass_mark)){
			$data['pass_mark'] = $request->pass_mark;
		}
		if(!empty($request->start_time)){
			$data['start_time'] = $request->start_time;
		}
		if(!empty($request->end_time)){
			$data['end_time'] = $request->end_time;
		}
		if(!empty($request->password)){
			$data['password'] = $request->password;
		}
		
		$update_data = $this->exam->update_record($request->id, $data, array('where' => array('owner_id' => $this->current_user->id)));
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
        
		$delete_data = $this->exam->delete_record($request->id, array('where' => array('owner_id' => $this->current_user->id)));
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
        
		$obj = $this->exam->search_record_by_id_object($request->id, array('where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess($obj);
	}
}