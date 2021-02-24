<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class SearchController extends FrontAuthController{
    public function byName(Request $request){
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
		$objs = $this->exam->search_record_paging(array('sort_id' => 'desc', 'select' => array('id','name'), 'fulltext' => array('name' => $request->name), 'where' => array('is_private' => 0, 'status' => 1)), $this->item_per_page, ($page - 1) * $this->item_per_page);
		$total = $this->exam->count_all_records(array('fulltext' => array('name' => $request->name), 'where' => array('is_private' => 0, 'status' => 1)));
		return $this->returnSuccess(array('objs' => $objs, 'total' => $total));
	}
}