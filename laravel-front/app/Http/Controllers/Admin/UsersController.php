<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10);

        $roles = Role::all();
        return view('admin.users', compact('users','roles'));
    }
}
