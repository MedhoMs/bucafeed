<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function index()
    {
        if (request()->ajax()) {
            return view('admin.index')->renderSections()['content'];
        }

        return view('admin.index');
    }

    public function users()
    {
        
        if (request()->ajax()) {
            return view('users.index')->renderSections()['content'];
        }

        return view('users.index');
    }


}
