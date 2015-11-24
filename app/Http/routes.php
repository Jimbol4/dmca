<?php


Route::get('/', 'PagesController@home');

Route::resource('notices', 'NoticesController');

Route::get('notices/create/confirm', 'NoticesController@confirm');

Route::controllers([
   'auth' => 'Auth\AuthController',
   'password' => 'Auth\PasswordController',
]);