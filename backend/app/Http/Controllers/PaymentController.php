<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentSubscribeRequest;
use App\Models\Payment;
use App\Models\Sub;
use App\Models\TelegramUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;

class PaymentController extends Controller
{
    public function subscribe (PaymentSubscribeRequest $request) {
        $user = User::where("user_id", $request["initData"]["user"]["id"])->first();
        $sub = Sub::where("type", "sub")->where("id", $request["subscription"])->firstOrFail();

        if (!$request->has("tokens")) $request["tokens"] = 0;

        $TOKEN_COST = 2;
        $price = $sub->price * $request["period"] - ($sub->price * $request["period"] * min(floor($request["period"] / 3)*0.05, 0.3))
            + $request["tokens"] * $TOKEN_COST;

        $client = new Client();
        $client->setAuth(env("SHOP_ID"), env("YOOKASSA_API_KEY"));

        $payment = Payment::create([
            "user_id" => $user->id,
            "payment_sum" => $price,
            "payment_date" => Carbon::now(),
            "type" => "sub",
            "sub_name" => $sub->name,
            "tokens" => $request["tokens"],
            "period" => $request["period"],
        ]);

        $response = $client->createPayment(
            [
                'amount' => [
                    'value' =>  number_format($payment->payment_sum, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => 'https://' . env("DOMAIN"),
                ],
                'capture' => true,
                'description' => 'Оплата платной услуги. Подписка: ' . $sub->name . ". Токены: " . $request["tokens"],
            ],
            $user->id . "_" . $payment->id . "_sub"
        );

        $payment->yookassa_id = $response->id;
        $payment->save();

        return response()->json(["url" => $response->confirmation->getConfirmationUrl()]);
    }

    public function webhook (Request $request) {
        $payment = Payment::where("yookassa_id", $request->object["id"])->first();
        if ($request->event === "payment.succeeded") {
            $user = User::find($payment->user_id);
            $sub = Sub::where("type", $payment->type)->where("name", $payment->sub_name)->first();

            $user->ai_tokens += $payment->tokens;
            if ($sub->type === "sub") {
                $user->sub_name = $sub->name;
                $user->date_sub = Carbon::now()->addMonths($payment->period);
            }

            $user->save();

            if ($user->referer_id !== null) {
                $referer = User::find($user->referer_id);

                $percent = 0.15;
                if (Sub::where("type", "sub")->where("name", $referer->sub_name)->first()->is_boost) $percent = 0.25;

                if ($referer->earning) $referer->earning = 0;
                $referer->earning += $payment->payment_sum * $percent;
                $referer->save();
            }
        }
        else if ($request->event === "payment.canceled") $payment->delete();

        $payment->save();
        return response()->json();
    }
}
