<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sub;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function my (Request $request) {
        $user = User::where("user_id", $request["initData"]["user"]["id"])->first();
        $referrals = User::where("referer_id", $user->id)->select("id", "username", "created_at")->get();
        $sum = User::where("referer_id", $user->id)->get()->sum("ai_tokens");

        $payments = 0;
        $count = 0;

        foreach ($referrals as $referral) {
            $pays = Payment::where("user_id", $referral->id)->get();

            $count += sizeof($pays);
            $payments += $pays->sum("payment_sum");
        }

        $boost = Sub::where("type", "sub")->where("if_active", 1)->where("is_boost", 1)->get();

        return response()->json([
            "partners" => $referrals,
            "sum" => $sum,
            "count" => $count,
            "payments" => $payments,
            "boost" => $boost,
        ]);
    }
}
