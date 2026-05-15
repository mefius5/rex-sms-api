<?php

use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('sms', SmsController::class)->only(['index', 'store']);
