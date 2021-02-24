<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontAuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class StatisticController extends FrontAuthController
{
	public function common(Request $request){
		$total_question = $this->question->count_all_records();
		$total_collection = $this->collection->count_all_records();
		$total_exam = $this->exam->count_all_records();
		$total_user = $this->user->count_all_records();
		$total_tested = $this->test->count_all_records(array('where' => array('status' => array('>', 0))));
		$total_buy = $this->test->count_all_records();
		$data = array(
			'total_question' => $total_question,
			'total_collection' => $total_collection,
			'total_exam' => $total_exam,
			'total_user' => $total_user,
			'total_tested' => $total_tested,
			'total_buy' => $total_buy,
		);
		return $this->returnSuccess($data);
	}
	public function topUpload(Request $request){
		$datas = array();
		$user_ids = array();
		$objs = $this->exam->search_record_paging(array(
			'select_raw' => 'owner_id, count(*) as counter',
			'group_by' => array('owner_id'),
			'order_raw' => 'COUNT(owner_id) DESC',
		), $this->item_per_page, 0);
		foreach($objs as $obj){
			$user_ids[] = $obj->owner_id;
		}
		if(count($user_ids) > 0){
			$list_user_objs = array();
			$user_objs = $this->user->search_all_records(array('select' => array('id', 'full_name', 'avatar'), 'where_in' => array('id' => $user_ids)));
			foreach($user_objs as $user_obj){
				$list_user_objs[$user_obj->id] = $user_obj;
			}
			foreach($objs as $obj_key => $obj){
				if(isset($list_user_objs[$obj->owner_id])){
					$cur_user = $list_user_objs[$obj->owner_id];
					$datas[] = array(
						'id' => $obj->owner_id,
						'counter' => $obj->counter,
						'full_name' => $cur_user->full_name,
						'avatar' => $cur_user->avatar,
					);
				}
			}
		}
		return $this->returnSuccess($datas);
	}
	public function topMark(Request $request){
		$objs = $this->test->search_record_paging(array(
			'select_raw' => 'owner_id, sum(got_mark) as sum_mark',
			'group_by' => array('owner_id'),
			'order_raw' => 'SUM(got_mark) DESC',
		), $this->item_per_page, 0);
		$datas = array();
		$user_ids = array();
		foreach($objs as $obj){
			$user_ids[] = $obj->owner_id;
		}
		if(count($user_ids) > 0){
			$list_user_objs = array();
			$user_objs = $this->user->search_all_records(array('select' => array('id', 'full_name', 'avatar'), 'where_in' => array('id' => $user_ids)));
			foreach($user_objs as $user_obj){
				$list_user_objs[$user_obj->id] = $user_obj;
			}
			foreach($objs as $obj_key => $obj){
				if(isset($list_user_objs[$obj->owner_id])){
					$cur_user = $list_user_objs[$obj->owner_id];
					$datas[] = array(
						'id' => $obj->owner_id,
						'sum_mark' => $obj->sum_mark,
						'full_name' => $cur_user->full_name,
						'avatar' => $cur_user->avatar,
					);
				}
			}
		}
		return $this->returnSuccess($datas);
	}
	public function forUser(Request $request){
		if(!empty($this->current_user)){
			$total_collection = $this->collection->count_all_records(array('where' => array('owner_id' => $this->current_user->id)));
			$total_exam = $this->exam->count_all_records(array('where' => array('owner_id' => $this->current_user->id)));
			$data = array(
				'total_collection' => $total_collection,
				'total_exam' => $total_exam,
			);
			return $this->returnSuccess($data);
		}else{
			return $this->returnAuthenError();
		}
	}
}