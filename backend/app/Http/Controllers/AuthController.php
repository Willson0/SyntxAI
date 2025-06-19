<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function profile (Request $request) {
        $user = User::where("telegram_id", $request["initData"]["user"]["id"])->first();
        if (!$user) {
            abort (401, "Напишите /start в бот для начала работы");
        }

        return response()->json($user);
    }
}
