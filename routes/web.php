<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return redirect()->route('dashboard.welcome');
});

Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');



