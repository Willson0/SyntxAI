<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TokenController;
use App\Http\Middleware\CheckTelegram;
use Illuminate\Support\Facades\Route;

Route::group (["prefix" => "api"], function () {
    Route::group (["prefix" => "auth", "middleware" => CheckTelegram::class], function () {
        Route::post("profile", [AuthController::class, "profile"]);
        Route::post("settings", [AuthController::class, "settings"]);
    });
    Route::group (["prefix" => "subscription"], function () {
        Route::get("", [SubscriptionController::class, "index"]);
        Route::get("/{sub}", [SubscriptionController::class, "show"]);
        Route::post("/subscribe", [PaymentController::class, "subscribe"])->middleware(CheckTelegram::class);
    });
    Route::group (["prefix" => "webhook"], function () {
        Route::post("/yookassa", [PaymentController::class, "webhook"]);
    });
    Route::group (["prefix" => "token"], function () {
        Route::get("/", [TokenController::class, "index"]);
        Route::post("/buy", [TokenController::class, "buy"])->middleware(CheckTelegram::class);
    });
    Route::group (["prefix" => "partner", "middleware" => CheckTelegram::class], function () {
        Route::post("/my", [PartnerController::class, "my"]);
    });
});
