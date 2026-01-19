<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-job', function () {
    \App\Jobs\SendMessageJob::dispatch();
    return 'Job dispatched!';
});
