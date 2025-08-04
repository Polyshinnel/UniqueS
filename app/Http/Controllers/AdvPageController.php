<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdvPageController extends Controller
{
    public function index()
    {
        return view('Adv.AdvListPage');
    }
}
