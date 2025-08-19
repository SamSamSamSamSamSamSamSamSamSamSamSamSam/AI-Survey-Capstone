<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DepartmentsController extends Controller
{
    public function index()
    {
        // replace with real department data later
        return view('admin.departments.index');
    }
}
