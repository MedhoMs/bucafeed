<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        //$libros = Libro::paginate(10);
        if ($request->ajax()) {
            return view('users.index');
        }
    }

    public function store(Request $request)
    {
       
    }

    public function show(User $user)
    {
        
    }

    public function update(Request $request, User $user)
    {

    }

    public function destroy(User $user)
    {

    }
}

