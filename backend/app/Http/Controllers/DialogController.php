<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DialogController extends Controller
{
    public function index (Request $request) {
        $user = User::where("user_id", $request["initData"]["user"]["id"])->first();

        $json = file_get_contents(base_path("../msges.json"));
        $json = json_decode($json, true);

        return response()->json($json[$user->id]);
    }

    public function destroy ($id, Request $request) {
        $user = User::where("user_id", $request["initData"]["user"]["id"])->first();

        $json = file_get_contents(base_path("../msges.json"));
        $json = json_decode($json, true);

        unset($json[$user->id][$id]);
        $json[$user->id] = array_values($json[$user->id]);

        file_put_contents(base_path("../msges.json"), json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json($json[$user->id]);
    }
}
