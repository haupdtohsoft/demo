<?php
namespace App\Helpers;

class CommonHelper
{
	public static function my_json_encode($objs){
		return json_encode($objs);
	}
	public static function my_json_decode($objs){
		return json_decode($objs);
	}
	public static function my_array_merge($arr1, $arr2){
		return array_merge($arr1, $arr2);
	}
	public static function pre($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}
	
    public static function my_reset($objs){
		if(count($objs) > 0){
			return $objs[0];
		}else{
			return null;
		}
	}
	public static function send_email($email, $content_mail, $title, $template){ 
		$sendgrid = [
			'driver' => env('MAIL_SENDGRID_DRIVER'),
			'host' => env('MAIL_SENDGRID_HOST'),
			'port' => env('MAIL_SENDGRID_PORT'),
			'username' => env('MAIL_SENDGRID_USERNAME'),
			'password' => env('MAIL_SENDGRID_PASSWORD'),
			'encryption' => env('MAIL_SENDGRID_ENCRYPTION'),
		];
		try {
			\Config::set('mail', $sendgrid);
			\Mail::send('emails/'.$template, $content_mail, function($message) use ($email, $title){
				$message->from(env('MAIL_USERNAME'), 'No-reply');
				$message->to($email)->subject($title);
			});
			return true;
		}catch(\Exception $e){
			return false;
		}
    }
	public static function generate_random_string($length = 10){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++){
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	public static function removePassword($objs){
		foreach($objs as $obj_key => $obj){
			if(isset($obj->password)){
				unset($objs[$obj_key]->password);
			}
		}
		return $objs;
	}
	public static function validQuestions($objs){
		if(is_array($objs)){
			$valid = TRUE;
			foreach($objs as $obj_key => $obj){
				$obj->id = ($obj_key + 1);
				$objs[$obj_key] = CommonHelper::validOneQuestion($obj);
				if($objs[$obj_key] === FALSE){
					$valid = FALSE;
					break;
				}
			}
			if($valid){
				return $objs;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	public static function validOneQuestion($obj){
		if(	!empty($obj->category_id) && 
			!empty($obj->name) && 
			!empty($obj->type) && 
			!empty($obj->content) && 
			!empty($obj->answers) && 
			!empty($obj->level)){
			$obj = CommonHelper::validAnswersOfOneQuestion($obj);
			return $obj;	
		}else{
			return FALSE;
		}
	}
	public static function validAnswersOfOneQuestion($obj){
		$answers = $obj->answers;
		if(is_array($answers)){
			if($obj->type == 1){
				$has_right = FALSE;
				$valid = TRUE;
				foreach($answers as $answer_key => $answer_obj){
					$answers[$answer_key]->id = ($answer_key + 1);
					if(!isset($answer_obj->content) || empty($answer_obj->content)){
						$valid = FALSE;
					}
					if(isset($answer_obj->right)){
						$has_right = TRUE;
					}
				}
				if($valid && $has_right){
					$obj->answers = CommonHelper::my_json_encode($answers);
					return $obj;
				}else{
					return FALSE;
				}
			}else if($obj->type == 2){
				$valid = TRUE;
				foreach($answers as $answer_key => $answer_obj){
					$answers[$answer_key]->id = ($answer_key + 1);
					if(!isset($answer_obj->answer) || empty($answer_obj->answer)){
						$valid = FALSE;
					}
				}
				if($valid){
					$obj->answers = CommonHelper::my_json_encode($answers);
					return $obj;
				}else{
					return FALSE;
				}
				return $obj;
			}else if($obj->type == 3){
				$valid = TRUE;
				foreach($answers as $answer_key => $answer_obj){
					$answers[$answer_key]->id = ($answer_key + 1);
					if(!isset($answer_obj->content) || empty($answer_obj->content)){
						$valid = FALSE;
					}
					if(!isset($answer_obj->position) || empty($answer_obj->position)){
						$valid = FALSE;
					}
				}
				if($valid){
					$obj->answers = CommonHelper::my_json_encode($answers);
					return $obj;
				}else{
					return FALSE;
				}
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	public static function validUserAnswers($objs, $question_objs){
		$valid = TRUE;
		$correct = 0;
		$incorrect = 0;
		$unanswer = 0;
		$list_questions = array();
		foreach($question_objs as $question_obj){
			$list_questions[$question_obj->id] = $question_obj;
		}
		foreach($objs as $obj){
			if(isset($obj->objs) && isset($obj->question_id) && isset($list_questions[$obj->question_id])){
				if(isset($list_questions[$obj->question_id])){
					$cur_question_obj = $list_questions[$obj->question_id];
					if($cur_question_obj->type == 1){
						if(count($obj->objs) > 0){
							$list_rights = array();
							$question_answers = CommonHelper::my_json_decode($cur_question_obj->answers);
							foreach($question_answers as $question_answer){
								if(isset($question_answer->right) && $question_answer->right){
									$list_rights[$question_answer->id] = 1;
								}
							}
							
							$list_user_rights = array();
							foreach($obj->objs as $user_answer_obj){
								if(isset($user_anser_obj->answer_id)){
									$list_user_rights[$user_anser_obj->answer_id] = 1;
								}
							}
							
							if(count($list_user_rights) > 0){
								if(count($list_user_rights) == count($list_rights)){
									$right = TRUE;
									foreach($list_user_rights as $list_user_right_key => $list_user_right_val){
										if(!isset($list_rights[$list_user_right_key])){
											$right = false;
										}
									}
									if($right){
										$correct++;
									}else{
										$incorrect++;
									}
								}else{
									$incorrect++;
								}
							}else{
								$unanswer++;
							}
						}else{
							$unanswer++;
						}
					}else if($cur_question_obj->type == 2){
						if(count($obj->objs) > 0){
							$list_rights = array();
							$question_answers = CommonHelper::my_json_decode($cur_question_obj->answers);
							foreach($question_answers as $question_answer){
								$list_rights[$question_answer->id] = $question_answer->answer;
							}
							
							$list_user_rights = array();
							foreach($obj->objs as $user_answer_obj){
								if(isset($user_anser_obj->answer_id) && isset($user_anser_obj->answer) && !empty($user_anser_obj->answer)){
									$list_user_rights[$user_anser_obj->answer_id] = $user_anser_obj->answer;
								}
							}
							
							if(count($list_user_rights) > 0){
								if(count($list_user_rights) == count($list_rights)){
									$right = TRUE;
									foreach($list_user_rights as $list_user_right_key => $list_user_right_val){
										if(!isset($list_rights[$list_user_right_key]) || $list_rights[$list_user_right_key] != $list_user_right_val){
											$right = false;
										}
									}
									if($right){
										$correct++;
									}else{
										$incorrect++;
									}
								}else{
									$incorrect++;
								}
							}else{
								$unanswer++;
							}
						}else{
							$unanswer++;
						}
					}else if($cur_question_obj->type == 3){
						if(count($obj->objs) > 0){
							$list_rights = array();
							$question_answers = CommonHelper::my_json_decode($cur_question_obj->answers);
							foreach($question_answers as $question_answer){
								$list_rights[$question_answer->id] = $question_answer->position;
							}
							
							$list_user_rights = array();
							foreach($obj->objs as $user_answer_obj){
								if(isset($user_anser_obj->answer_id) && isset($user_anser_obj->position) && !empty($user_anser_obj->position)){
									$list_user_rights[$user_anser_obj->answer_id] = $user_anser_obj->position;
								}
							}
							
							if(count($list_user_rights) > 0){
								if(count($list_user_rights) == count($list_rights)){
									$right = TRUE;
									foreach($list_user_rights as $list_user_right_key => $list_user_right_val){
										if(!isset($list_rights[$list_user_right_key]) || $list_rights[$list_user_right_key] != $list_user_right_val){
											$right = false;
										}
									}
									if($right){
										$correct++;
									}else{
										$incorrect++;
									}
								}else{
									$incorrect++;
								}
							}else{
								$unanswer++;
							}
						}else{
							$unanswer++;
						}
					}else{
						$valid = FALSE;
						break;
					}
				}
			}else{
				$valid = FALSE;
				break;
			}
		}
		if($valid){
			return (object)array(
				'correct' => $correct,
				'incorrect' => $incorrect,
				'unanswer' => $unanswer
			);
		}else{
			return FALSE;
		}
	}
	public static function removeAnswersInQuestions($objs){
		foreach($objs as $obj_key => $obj){
			$answers = CommonHelper::my_json_decode($obj->answers);
			if($obj->type == 1){
				foreach($answers as $answer_key => $answer_value){
					$answers[$answer_key]->right = 1;
				}
			}else if($obj->type == 2){
				foreach($answers as $answer_key => $answer_value){
					$answers[$answer_key]->answer = '';
				}
			}else if($obj->type == 3){
				foreach($answers as $answer_key => $answer_value){
					$answers[$answer_key]->position = 1;
				}
			}
			$objs[$obj_key]->answers = CommonHelper::my_json_encode($answers);
		}
		return $objs;
	}
}