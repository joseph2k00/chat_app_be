<?php

use App\Events\UserSentMessageEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    UserSentMessageEvent::dispatch();
    return view('welcome');
});
