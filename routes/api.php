<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Authen api
Route::post('auth/login', 'AuthController@login');
Route::post('auth/register', 'AuthController@register');
Route::post('auth/forgot-password', 'AuthController@forgotPassword');
Route::post('auth/confirm-forgot-password', 'AuthController@confirmForgotPassword');
Route::post('auth/update-info', 'AuthController@updateInfo');

//Super admin
Route::group(['prefix' => 'admin/category'], function(){
	Route::get('get-info/{id}', 'admin\CategoryController@getInfo');
	Route::get('list', 'admin\CategoryController@list');
	Route::get('delete/{id}', 'admin\CategoryController@delete');
	Route::post('create', 'admin\CategoryController@create');
	Route::post('update/{id}', 'admin\CategoryController@update');
});
Route::group(['prefix' => 'admin/report'], function(){
    Route::get('get-info/{id}', 'admin\ReportController@getInfo');
	Route::get('list', 'admin\ReportController@list');
	Route::get('delete/{id}', 'admin\ReportController@delete');
	Route::post('create', 'admin\ReportController@create');
	Route::post('update/{id}', 'admin\ReportController@update');
});
Route::group(['prefix' => 'admin/user'], function(){
    Route::get('get-info/{id}', 'admin\UserController@getInfo');
	Route::get('list', 'admin\UserController@list');
	Route::get('delete/{id}', 'admin\UserController@delete');
	Route::post('create', 'admin\UserController@create');
	Route::post('update/{id}', 'admin\UserController@update');
});

//Backend api
Route::group(['prefix' => 'back/collection'], function(){
	Route::get('get-info/{id}', 'back\CollectionController@getInfo');
	Route::get('list', 'back\CollectionController@list');
	Route::get('search', 'back\CollectionController@search');
	Route::get('delete/{id}', 'back\CollectionController@delete');
	Route::post('create', 'back\CollectionController@create');
	Route::post('update/{id}', 'back\CollectionController@update');
});
Route::group(['prefix' => 'back/exam'], function(){
	Route::get('get-info/{id}', 'back\ExamController@getInfo');
	Route::get('list', 'back\ExamController@list');
	Route::get('search', 'back\ExamController@search');
	Route::get('delete/{id}', 'back\ExamController@delete');
	Route::post('create', 'back\ExamController@create');
	Route::post('update/{id}', 'back\ExamController@update');
});
Route::group(['prefix' => 'back/question'], function(){
    Route::get('get-info/{id}', 'back\QuestionController@getInfo');
	Route::get('list', 'back\QuestionController@list');
	Route::get('search', 'back\QuestionController@search');
	Route::get('nosearch', 'back\QuestionController@nosearch');
	Route::get('delete/{id}', 'back\QuestionController@delete');
	Route::post('create', 'back\QuestionController@create');
	Route::post('update/{id}', 'back\QuestionController@update');
});

//Frontend api
Route::group(['prefix' => 'front/exam'], function(){
	Route::get('get-info/{id}', 'front\ExamController@getInfo');
	Route::get('top-exam', 'front\ExamController@topExam');
	Route::get('new-exam', 'front\ExamController@newExam');
	Route::get('list-by-category/{id}', 'front\ExamController@listByCategory');
	Route::get('list-by-collection/{id}', 'front\ExamController@listByCollection');
	Route::get('list-by-user/{id}', 'front\ExamController@listByUser');
});
Route::group(['prefix' => 'front/test'], function(){
	Route::get('get-info/{id}', 'front\TestController@getInfo');
	Route::get('list-by-exam/{id}', 'front\TestController@listByExam');
	Route::post('create', 'front\TestController@create');
	Route::post('update/{id}', 'front\TestController@update');
});

Route::group(['prefix' => 'front/search'], function(){
	Route::get('by-name', 'front\SearchController@byName');
});
Route::group(['prefix' => 'front/category'], function(){
	Route::get('list', 'front\CategoryController@list');
	Route::get('get-info/{id}', 'front\CategoryController@getInfo');
});
Route::group(['prefix' => 'front/collection'], function(){
	Route::get('get-info/{id}', 'front\CollectionController@getInfo');
	Route::get('list-by-category/{id}', 'front\CollectionController@listByCategory');
});
Route::group(['prefix' => 'front/report'], function(){
	Route::post('create', 'front\ReportController@create');
});
Route::group(['prefix' => 'front/favorite'], function(){
	Route::get('create/{id}', 'front\FavoriteController@create');
	Route::get('delete/{id}', 'front\FavoriteController@delete');
});
Route::group(['prefix' => 'front/statistic'], function(){
	Route::get('common', 'front\StatisticController@common');
	Route::get('top-upload', 'front\StatisticController@topUpload');
	Route::get('top-mark', 'front\StatisticController@topMark');
	Route::get('for-user', 'front\StatisticController@forUser');
});