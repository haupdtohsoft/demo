<?php

namespace App\Http\Controllers\Back;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BackAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class QuestionController extends BackAuthController{
    public function list(Request $request){
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->question->search_record_paging(array('sort_id' => 'desc', 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->question->count_all_records(array('where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    public function search(Request $request){
		$rules = [
			'name' => 'required|max:255',
		];
		$messages = [
			'name.required'   => __('Bạn chưa nhập tên Câu hỏi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->question->search_record_paging(array('sort_id' => 'desc', 'fulltext' => array('content' => $request->name), 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->question->count_all_records(array('select' => array('id','content'), 'fulltext' => array('content' => $request->name), 'where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
	
	public function nosearch(Request $request){
		$rules = [
			'name' => 'required|max:255',
		];
		$messages = [
			'name.required'   => __('Bạn chưa nhập tên Câu hỏi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$page = !empty($request->page) ? $request->page : 1;
		$objs = $this->question->search_record_paging(array('nocache' => true, 'sort_id' => 'desc', 'fulltext' => array('content' => $request->name), 'where' => array('owner_id' => $this->current_user->id)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->question->count_all_records(array('nocache' => true, 'select' => array('id','content'), 'fulltext' => array('content' => $request->name), 'where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
    
	
    public function create(Request $request){
		$rules = [
			'category_id' => 'required',
			'name' => 'required|max:255',
			'type' => 'required',
			'content' => 'required',
			'answers' => 'required',
			'level' => 'required',
		];
		$messages = [
			'category_id.required'   => __('Bạn chưa chọn Danh mục!'),
            'type.required'   => __('Bạn chưa chọn Kiểu bài thi!'),
            'name.required'   => __('Bạn chưa nhập tên Câu hỏi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
			'content.required'   => __('Bạn chưa nhập Nội dung!'),
			'answers.required'   => __('Bạn chưa nhập Câu trả lời!'),
			'level.required'   => __('Bạn chưa chọn độ khó của câu hỏi!'),
		];
		$validator = Validator::make($request->all(), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$answers = CommonHelper::validAnswersOfOneQuestion((object)array('type' => $request->type, 'answers' => CommonHelper::my_json_decode($request->answers)));
		if($answers === FALSE){
			$error_data = array('answers' => array(__('Câu trả lời sai định dạng hoặc chưa chọn đáp án đúng!')));
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
			'content' => $request->content,
			'answers' => CommonHelper::my_json_encode($answers),
			'level' => $request->level,
		);
		if(!empty($request->tag)){
			$data['tag'] = $request->tag;
		}
		if(!empty($request->explain)){
			$data['explain'] = $request->explain;
		}
		
		$insert_id = $this->question->insert_record($data);
		return $this->returnSuccess($insert_id);
	}
	public function update(Request $request){
		$rules = [
			'id' => 'required',
			'category_id' => 'required',
			'name' => 'required|max:255',
			'type' => 'required',
			'content' => 'required',
			'answers' => 'required',
			'level' => 'required',
		];
		$messages = [
			'id.required'	  => __('Bạn cần nhập ID!'),
			'category_id.required'   => __('Bạn chưa chọn Danh mục!'),
            'type.required'   => __('Bạn chưa chọn Kiểu bài thi!'),
            'name.required'   => __('Bạn chưa nhập tên Câu hỏi!'),
            'name.max'        => __('Tên Bài thi không được vượt quá 255 ký tự!'),
			'content.required'   => __('Bạn chưa nhập Nội dung!'),
			'answers.required'   => __('Bạn chưa nhập Câu trả lời!'),
			'level.required'   => __('Bạn chưa chọn độ khó của câu hỏi!'),
		];
		$validator = Validator::make(array_merge($request->all(), array('id' => $request->id)), $rules, $messages);
		if($validator->fails()){
			return $this->returnError('Invalid Data', $validator->errors()->getMessages());
		}
		
		$answers = CommonHelper::validAnswersOfOneQuestion((object)array('type' => $request->type, 'answers' => CommonHelper::my_json_decode($request->answers)));
		if($answers === FALSE){
			$error_data = array('answers' => array(__('Câu trả lời sai định dạng hoặc chưa chọn đáp án đúng!')));
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
			'content' => $request->content,
			'answers' => CommonHelper::my_json_encode($answers),
			'level' => $request->level,
		);
		if(!empty($request->tag)){
			$data['tag'] = $request->tag;
		}
		if(!empty($request->explain)){
			$data['explain'] = $request->explain;
		}
		
		$update_data = $this->question->update_record($request->id, $data, array('where' => array('owner_id' => $this->current_user->id)));
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
        
		$delete_data = $this->question->delete_record($request->id, array('where' => array('owner_id' => $this->current_user->id)));
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
        
		$obj = $this->question->search_record_by_id_object($request->id, array('where' => array('owner_id' => $this->current_user->id)));
		return $this->returnSuccess($obj);
	}
}