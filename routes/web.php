<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
Route::get('/custom-login', function() {
    return view('auth.custom-login');
})->name('custom-login');


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.welcome');
    }

    return redirect()->route('login');
});

Auth::routes(['register' => false]);

// Route::get('/home', 'HomeController@index')->name('home');



