<?php

namespace App\Http\Controllers;

use App\Http\Requests\TokenBuyRequest;
use App\Models\Payment;
use App\Models\Sub;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use YooKassa\Client;

class TokenController extends Controller
{
    public function index () {
        $query = Sub::where("type", "token")->where("if_active", 1)->get()->toArray();
        $result = array_reduce($query, function($carry, $item) {
            $carry[$item['ai_tokens']] = $item['price'];
            return $carry;
        });
        return response()->json($result);
    }

    public function buy (TokenBuyRequest $request) {
        $user = User::where("user_id", $request["initData"]["user"]["id"])->first();
        $tariff = Sub::where("type", "token")->where("ai_tokens", $request["tokens"])->firstOrFail();

        $client = new Client();
        $client->setAuth(env("SHOP_ID"), env("YOOKASSA_API_KEY"));

        $payment = Payment::create([
            "user_id" => $user->id,
            "payment_sum" => $tariff->price,
            "payment_date" => Carbon::now(),
            "type" => "sub",
            "sub_name" => $tariff->name,
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
                'description' => 'Оплата платной услуги. Токены: ' . $request["tokens"],
            ],
            uniqid('', true)
        );

//        $payment->yookassa_id = $response->id;
        $payment->save();

        return response()->json(["url" => $response->confirmation->getConfirmationUrl()]);
    }

    public function withdraw (Request $request) {

    }
}
