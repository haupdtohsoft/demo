<?php

namespace App\Http\Controllers;

class BackAuthController extends AuthController{
	public function __construct(){
		parent::__construct();
		if(empty($this->current_user)){
			die($this->returnAuthenError());
		}
	}
}