<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GuidesUsers extends Controller
{
    public function index()
    {
        return view('Guides.GuidesUsersPage');
    }
}
