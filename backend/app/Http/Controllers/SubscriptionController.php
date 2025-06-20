<?php

namespace App\Http\Controllers;

use App\Models\Sub;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index () {
        return Sub::all();
    }
}
