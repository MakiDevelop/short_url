<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PoliciesController extends Controller
{
    public function __construct()
    {

    }

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
    }
}
