<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::post('/signup', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    return redirect()->route('signup')->with('success', 'Signup data submitted!');
})->name('signup.submit');
