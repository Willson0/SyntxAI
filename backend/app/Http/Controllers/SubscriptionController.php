<?php

namespace App\Http\Controllers;

use App\Models\Sub;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index () {
        return Sub::where("type", "sub")->where("if_active", 1)->get();
    }

    public function show (Sub $sub) {
        return $sub;
    }
}
