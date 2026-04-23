<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;

class UserImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle); // Skip header

        $rows = [];
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);

        // 1. PRE-VALIDATION: Check for duplicates or empty fields before touching the DB
        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // +2 because of header and 0-index
            
            // Check for empty columns
            if (empty($row[0]) || empty($row[2])) {
                return back()->withErrors("Line $lineNumber: ID Number and Email are required.");
            }

            // Check if ID or Email already exists in DB
            if (\App\Models\User::where('user_id_number', $row[0])->exists()) {
                return back()->withErrors("Line $lineNumber: ID Number {$row[0]} is already taken.");
            }
            if (\App\Models\User::where('email', $row[2])->exists()) {
                return back()->withErrors("Line $lineNumber: Email {$row[2]} is already registered.");
            }
        }

        // 2. PROCESSING: If we reach here, the whole file is "clean"
        // SECURITY FIX: Queue emails asynchronously to prevent timeouts and memory issues
        \Illuminate\Support\Facades\DB::transaction(function () use ($rows) {
            foreach ($rows as $data) {
                $user = \App\Models\User::create([
                    'user_id_number' => trim($data[0]),
                    'name'           => trim($data[1]),
                    'email'          => trim($data[2]),
                    'password'       => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'must_change_password' => true, 
                ]);

                $roleName = strtolower(trim($data[3]));
                $role = \App\Models\Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }

                // SECURITY FIX: Queue email instead of sending synchronously
                $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUserMail($user, $token));
            }
        });

        // 3. SUCCESS BANNER: Flash a message to the session
        return redirect()->route('admin.users.index')->with('success', count($rows) . ' users have been successfully imported and notified!');
    }

    public function showImportForm()
    {
        return view('admin.users.import');
    }
}