<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentSubscribeRequest;
use App\Models\Payment;
use App\Models\Sub;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;

class PaymentController extends Controller
{
    public function subscribe (PaymentSubscribeRequest $request) {
        if (!$request->has("subscription")) abort(422);

        $user = TelegramUser::where("telegram_id", $request["initData"]["user"]["id"])->firstOrFail()->user;

        $sub = Sub::where("type", "sub")->where("name", $request["subscription"])->firstOrFail();


        $client = new Client();
        $client->setAuth(env("SHOP_ID"), env("YOOKASSA_API_KEY"));

        $payment = Payment::create([
            "user_id" => $user->id,
            "amount" => $subs[$subscription],
            "yookassa_id" => "creating...",
            "status" => 0,
            "sub_lvl" => $subscription,
        ]);

        $response = $client->createPayment(
            [
                'amount' => [
                    'value' =>  number_format($payment->amount, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => 'https://' . env("DOMAIN"),
                ],
                'capture' => true,
                'description' => 'Оплата платной услуги. Уровень: ' . ($subscription+1),
            ],
            uniqid('', true)
        );

        $payment->yookassa_id = $response->id;
        $payment->save();

        return response()->json(["url" => $response->confirmation->getConfirmationUrl()]);
    }

    public function webhook (Request $request) {
        Log::critical($request);
        $payment = Payment::where("yookassa_id", $request->object["id"])->first();
        if ($request->event === "payment.succeeded") {
            $payment->status = 1;

            $user = $payment->user;

            $user->subscription = $payment->sub_lvl;
            if ($user->subscription == 2) $user->listen_tokens += 10;
            else if ($user->subscription == 3) $user->listen_tokens = 1000;

            $user->save();
        }
        else if ($request->event === "payment.canceled") $payment->delete();

        $payment->save();
        return response()->json();
    }
}
