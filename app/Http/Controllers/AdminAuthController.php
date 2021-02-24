<?php

namespace App\Http\Controllers;

class AdminAuthController extends AuthController{
	public function __construct(){
		parent::__construct();
		if(empty($this->current_user) || $this->current_user->id != $this->super_admin_id){
			die($this->returnAuthenError());
		}
	}
}