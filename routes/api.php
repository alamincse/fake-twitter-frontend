<?php

use App\Http\Controllers\Api\FakeTwitterController;
use Illuminate\Support\Facades\Route;


Route::post('login', [FakeTwitterController::class, 'login'])->name('fake_twitter.login');
Route::post('register', [FakeTwitterController::class, 'register'])->name('fake_twitter.register');


Route::middleware('auth:api')->group(function () {
    Route::post('profile', [FakeTwitterController::class, 'profile'])->name('fake_twitter.profile');
    Route::post('profile-update', [FakeTwitterController::class, 'updateProfile'])->name('fake_twitter.profile_update');

    Route::post('tweet', [FakeTwitterController::class, 'postTweet'])->name('fake_twitter.tweet');
    Route::get('tweet', [FakeTwitterController::class, 'getTweets'])->name('fake_twitter.tweets');
    Route::get('my-tweet', [FakeTwitterController::class, 'getMyTweets'])->name('fake_twitter.my_tweets');

    Route::post('like', [FakeTwitterController::class, 'doLike'])->name('fake_twitter.like');
    Route::post('unlike', [FakeTwitterController::class, 'doUnlike'])->name('fake_twitter.unlike');

    Route::post('follow', [FakeTwitterController::class, 'doFollow'])->name('fake_twitter.follow');
    Route::post('unfollow', [FakeTwitterController::class, 'doUnfollow'])->name('fake_twitter.unfollow');


    Route::post('follower', [FakeTwitterController::class, 'getFollowers'])->name('fake_twitter.follower');
    Route::post('following', [FakeTwitterController::class, 'getFollowing'])->name('fake_twitter.following');

    Route::post('search', [FakeTwitterController::class, 'search'])->name('fake_twitter.search');

    Route::post('refresh-token', [FakeTwitterController::class, 'refreshToken'])->name('fake_twitter.refresh_token');

    Route::post('logout', [FakeTwitterController::class, 'logout'])->name('fake_twitter.logout');
});
