<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function settings(Request $request) {
        if ($request->method() == 'GET') {
            return view('settings.index');
        } else {

        }
    }

    public function bin(Request $request) {
        if ($request->method() == 'GET') {
            return view('bin.index');
        } else {
            
        }
    }
}
