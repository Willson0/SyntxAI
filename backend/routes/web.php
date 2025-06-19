<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckTelegram;
use Illuminate\Support\Facades\Route;

Route::group (["prefix" => "api"], function () {
    Route::group (["prefix" => "auth", "middleware" => CheckTelegram::class], function () {
        Route::post("profile", [AuthController::class, "profile"]);
    });
});
