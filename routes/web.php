<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
Route::get('/custom-login', function() {
    return view('auth.custom-login');
})->name('custom-login');


Route::get('/', function () {
    return redirect()->route('dashboard.welcome');
});

Auth::routes(['register' => false]);

// Route::get('/home', 'HomeController@index')->name('home');



