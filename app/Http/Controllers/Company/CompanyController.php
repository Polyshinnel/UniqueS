<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return view('Company.CompanyPage');
    }

    public function show()
    {
        return view('Company.CompanyShowPage');
    }
}
