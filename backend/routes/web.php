<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\CheckTelegram;
use Illuminate\Support\Facades\Route;

Route::group (["prefix" => "api"], function () {
    Route::group (["prefix" => "auth", "middleware" => CheckTelegram::class], function () {
        Route::post("profile", [AuthController::class, "profile"]);
        Route::post("settings", [AuthController::class, "settings"]);
    });
    Route::group (["prefix" => "subscription"], function () {
        Route::get("", [SubscriptionController::class, "index"]);
    });
});
