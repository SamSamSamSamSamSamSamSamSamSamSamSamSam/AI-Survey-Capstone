<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::select('id','name','email','role')->limit(100)->get();
        return view('admin.users.index', compact('users'));
    }
}

