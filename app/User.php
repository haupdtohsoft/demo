<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use App\MyModel;

class User extends MyModel implements JWTSubject{
	protected $hidden = ['password', 'forgot_password_token'];
	protected $model_class = User::class;
	
    public function getJWTIdentifier(){
        return $this->getKey();
    }
	public function getJWTCustomClaims(){
        return [];
    }
}
