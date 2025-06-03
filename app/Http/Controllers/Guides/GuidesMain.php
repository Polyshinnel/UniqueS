<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GuidesMain extends Controller
{
    public function index()
    {
        return view('Guides.GuidesMainPage');
    }
}
