<?php

use App\Http\Controllers\FakeTwitterController;
use Illuminate\Support\Facades\Route;


Route::get('/', [FakeTwitterController::class, 'login'])->name('login');
Route::get('/register', [FakeTwitterController::class, 'register'])->name('register');

Route::group(['middleware' => ['check_login']], function () {
    Route::get('/dashboard', [FakeTwitterController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [FakeTwitterController::class, 'profile'])->name('profile');
    Route::get('/follower', [FakeTwitterController::class, 'follower'])->name('follower');
    Route::get('/following', [FakeTwitterController::class, 'following'])->name('following');
    Route::get('/tweet', [FakeTwitterController::class, 'tweet'])->name('tweet');
});
