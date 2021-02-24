<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use URL;

use App\Helpers\CommonHelper;

class InitDataController extends AuthController{
	public function initdata(){
		set_time_limit(-1);
		ini_set('max_execution_time', -1);
		
		//$this->initCategory();
		//$this->initReport();
		//$this->convertOldquestion();
		//$this->initQuestion();
		//$this->initExam();
		//$this->initCollection();
		//$this->initTest();
		die('init');
	}
	
	public function convertOldquestion(){
		$limit = 1000;
		$offset = 0;
		$old_objs = array();
		while(true){
			$old_objs = $this->oldquestion->search_record_paging(array(), $limit, $offset);
			if(!empty($old_objs)){
				$offset += count($old_objs);
				$this->insertOldQuestion($old_objs);
			}else{
				break;
			}
		}
	}
	public function insertOldQuestion($old_objs){
		if(count($old_objs) > 0){
			foreach($old_objs as $old_obj){
				$old_answers = CommonHelper::my_json_decode($old_obj->answer);
				if(is_array($old_answers)){
					$old_answer_correct = CommonHelper::my_json_decode($old_obj->answer_correct);
					if($old_obj->type == 1){
						$answers = array();
						$answer_counter = 1;
						$has_right_answer = false;
						foreach($old_answers as $old_key => $old_val){
							$cur_answer = (object)array(
								'id' => $answer_counter,
								'content' => $old_val,
							);
							if(isset($old_answer_correct->{$old_key})){
								$cur_answer->right = 1;
								$has_right_answer = true;
							}
							$answers[] = $cur_answer;
							$answer_counter++;
						}
						if(!$has_right_answer){
							$answers = array();
						}
					}else if($old_obj->type == 2){
						$answers = array();
						$answer_counter = 1;
						foreach($old_answers as $old_key => $old_val){
							if(!empty($old_val)){
								$cur_answer = (object)array(
									'id' => $answer_counter,
									'content' => '',
									'answer' => $old_val,
								);
								$answers[] = $cur_answer;
								$answer_counter++;
							}
						}
					}else if($old_obj->type == 3){
						$answers = array();
						$answer_counter = 1;
						foreach($old_answers as $old_key => $old_val){
							if(!empty($old_val)){
								$cur_answer = (object)array(
									'id' => $answer_counter,
									'content' => $old_val,
									'position' => $answer_counter,
								);
								$answers[] = $cur_answer;
								$answer_counter++;
							}
						}
					}
					if(count($answers) > 0){
						$data = array(
							'id' => $old_obj->id,
							'owner_id' => $this->super_admin_id,
							'name' => '',
							'category_id' => 1,
							'type' => $old_obj->type,
							'content' => $old_obj->content,
							'answers' => CommonHelper::my_json_encode($answers),
							'level' => empty($old_obj->level) ? 1 : $old_obj->level,
							'tag' => '',
							'explain' => $old_obj->explain,
							'media' => $old_obj->media,
						);
						$this->question->insert_record($data);
					}
				}
			}
		}
	}
	
	public function initTest(){
		$answers = array();
		$question_objs = $this->question->search_record_paging(array(), 2, 0);
		foreach($question_objs as $question_obj){
			if($question_obj->type == 1){
				$cur_answer = array(
					'question_id' => $question_obj->id,
					'objs' => array(
						(object)array('answer_id' => 1),
					),
				);
			}
			if($question_obj->type == 2){
				$cur_answer = array(
					'question_id' => $question_obj->id,
					'objs' => array(
						(object)array('answer_id' => 1, 'answer' => 'Answer 1'),
					),
				);
			}
			if($question_obj->type == 3){
				$cur_answer = array(
					'question_id' => $question_obj->id,
					'objs' => array(
						(object)array('answer_id' => 1, 'position' => 1),
					),
				);
			}
			$answers[] = $cur_answer;
		}
		for($i = 0; $i < 10000; $i++){
			$datas = array();
			for($j = 0; $j < 10; $j++){
				$datas[] = array(
					'owner_id' => $this->super_admin_id,
					'exam_id' => 1,
					'owner_exam_id' => 1,
					'answers' => CommonHelper::my_json_encode($answers),
					'start_time' => date('Y-m-d H:i:s'),
					'end_time' => date('Y-m-d H:i:s'),
					'status' => 1,
				);
			}
			$this->test->insert_batch($datas);
		}
	}
	public function initCollection(){
		$exams_ids= array();
		$exam_objs = $this->exam->search_record_paging(array(), 20, 0);
		foreach($exam_objs as $exam_obj){
			$exams_ids[] = $exam_obj->id;
		}
		$exams = CommonHelper::my_json_encode($exams_ids);
		for($i = 0; $i < 1000; $i++){
			$datas = array();
			for($j = 0; $j < 100; $j++){
				$datas[] = array(
					'owner_id' => $this->super_admin_id,
					'name' => 'Collection '.$i,
					'category_id' => 1,
					'exams' => $exams,
					'tag' => 'Tag '.$i,
					'description' => 'Description '.$i,
					'status' => 1,
					'price' => 100000,
					'is_private' => 0,
					'password' => '',
				);
			}
			$this->collection->insert_batch($datas);
		}
	}
	public function initExam(){
		$questions = $this->question->search_record_paging(array(), 2, 0);
		for($i = 0; $i < 1000; $i++){
			$datas = array();
			for($j = 0; $j < 100; $j++){
				$datas[] = array(
					'owner_id' => $this->super_admin_id,
					'name' => 'Exam '. $i,
					'category_id' => 1,
					'type' => 0,
					'questions' => CommonHelper::my_json_encode($questions),
					'total_mark' => 100,
					'total_question' => $questions === null ? 0 : count($questions),
					'tag' => 'Tag '.$i,
					'description' => 'Description '.$i,
					'show_answer' => 1,
					'price_discount' => 10,
					'status' => 1,
					'price' => 100000,
					'time' => 0,
					'is_private' => 0,
					'pass_mark' => 80,
					'start_time' => null,
					'end_time' => null,
					'password' => '',
				);
			}
			$this->exam->insert_batch($datas);
		}
	}
	public function initQuestion(){
		for($i = 0; $i < 10; $i++){
			$limit = 100;
			$offset = 0;
			$max_offset = 100000;
			$objs = array();
			while(true){
				$objs = $this->question->search_record_paging(array(), $limit, $offset);
				if(!empty($objs) && $offset < $max_offset){
					$offset += count($objs);
					$datas = array();
					foreach($objs as $obj){
						$datas[] = array(
							"category_id" => $obj->category_id,
							"owner_id" => $obj->owner_id,
							"name" => $obj->name,
							"type" => $obj->type,
							"tag" => $obj->tag,
							"content" => $obj->content,
							"explain" => $obj->explain,
							"answers" => $obj->answers,
							"media" => $obj->media,
							"level" => $obj->level,
							"deleted_at" => $obj->deleted_at,
							"created_at" => $obj->created_at,
							"updated_at" => $obj->updated_at,
						);
					}
					$this->question->insert_batch($datas);
				}else{
					break;
				}
			}
		}
	}
	public function initReport(){
		for($i = 0; $i < 10000; $i++){
			$this->report->insert_record(array(
				'owner_id' => $this->super_admin_id,
				'question_id' => 1,
				'description' => 'Test '.$i,
			));
		}
	}
	public function initCategory(){
	   $objs = (object)array(
			(object)array('name' => 'Tiểu học - THCS - THPT', 'position' => 1, 'objs' => (object)array(
				(object)array('name' => 'Thi THPT Quốc Gia', 'position' => 1),
				(object)array('name' => 'Lớp 5', 'position' => 1),
				(object)array('name' => 'Lớp 4', 'position' => 1),
				(object)array('name' => 'Lớp 3', 'position' => 1),
				(object)array('name' => 'Lớp 2', 'position' => 1),
				(object)array('name' => 'Lớp 1', 'position' => 1),
				(object)array('name' => 'Lớp 6', 'position' => 1),
				(object)array('name' => 'Lớp 7', 'position' => 1),
				(object)array('name' => 'Lớp 8', 'position' => 1),
				(object)array('name' => 'Lớp 9', 'position' => 1),
				(object)array('name' => 'Lớp 10', 'position' => 1),
				(object)array('name' => 'Lớp 11', 'position' => 1),
				(object)array('name' => 'Lớp 12', 'position' => 1),
			)),
			(object)array('name' => 'Đề Thi Tuyển Dụng', 'position' => 2, 'objs' => (object)array(
				(object)array('name' => 'Tuyển dụng Ngân hàng', 'position' => 1),
				(object)array('name' => 'Tuyển dụng Công chức', 'position' => 1),
			)),
			(object)array('name' => 'Đại học - Cao đẳng', 'position' => 3, 'objs' => (object)array(
				(object)array('name' => 'Đại học Công nghệ Đồng Nai', 'position' => 1),
				(object)array('name' => 'ĐH Kinh Tế Quốc Dân - NEU', 'position' => 1),
			)),
			(object)array('name' => 'Ngoại Ngữ', 'position' => 4, 'objs' => (object)array(
				(object)array('name' => 'Tiếng Trung', 'position' => 1),
				(object)array('name' => 'Tiếng Hàn Quốc', 'position' => 1),
				(object)array('name' => 'Tiếng Nhật Bản', 'position' => 1),
				(object)array('name' => 'Tiếng Anh', 'position' => 1),
			)),
		);
		foreach($objs as $obj){
			$cur_obj = clone $obj;
			$cur_obj->owner_id = $this->super_admin_id;
			unset($cur_obj->objs);
			$insert_id = $this->category->insert_record((array)$cur_obj);
			foreach($obj->objs as $sub_obj){
			   $cur_sub_obj = clone $sub_obj;
			   $cur_sub_obj->owner_id = $this->super_admin_id;
			   $cur_sub_obj->parent_id = $insert_id;
			   unset($cur_sub_obj->objs);
			   $this->category->insert_record((array)$cur_sub_obj);
			}
		}
	}
	public function getRandomQuestion($i){
		$type = rand(1, 3);
		if($type == 1){
			$answers = array();
			for($j = 1; $j < 5; $j++){
				$answers[] = (object)array('id' => $j, 'content' => 'Content '.$j, 'right' => $j == 1 ? 1 : 0);
			}
		}
		if($type == 2){
			$answers = array();
			for($j = 1; $j < 5; $j++){
				$answers[] = (object)array('id' => $j, 'content' => 'Content '.$j, 'answer' => 'Answer '.$j);
			}
		}
		if($type == 3){
			$answers = array();
			for($j = 1; $j < 5; $j++){
				$answers[] = (object)array('id' => $j, 'content' => 'Content '.$j, 'position' => $j);
			}
		}
		$data = array(
			'owner_id' => $this->super_admin_id,
			'name' => 'Question '.$i,
			'category_id' => 1,
			'type' => $type,
			'content' => 'Content '. $i,
			'answers' => CommonHelper::my_json_encode($answers),
			'level' => 1,
			'tag' => 'Tag '.$i,
			'explain' => 'Explain '.$i,
		);
		return $data;
	}
}