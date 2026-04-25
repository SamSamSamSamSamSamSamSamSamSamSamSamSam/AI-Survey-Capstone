<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\WelcomeUserMail;

class UserImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $requiredHeaders = ['id', 'name', 'email', 'role']; // Define your expected headers

        if (!$header || count(array_intersect($requiredHeaders, array_map('strtolower', $header))) < 4) {
            fclose($handle);
            return back()->withErrors("Invalid CSV format. Please ensure your file has headers: " . implode(', ', $requiredHeaders));
        }

        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Basic validation
            if (empty($data[0]) || empty($data[2])) {
                $results['errors'][] = "Skipped row: Missing required fields.";
                $results['skipped']++;
                continue;
            }

            // Check if exists
            $exists = User::where('user_id_number', trim($data[0]))
                ->orWhere('email', trim($data[2]))
                ->exists();

            if ($exists) {
                $results['skipped']++;
                continue;
            }

            // Process only if new
            DB::transaction(function () use ($data, &$results) {
                $user = User::create([
                    'user_id_number' => trim($data[0]),
                    'name'           => trim($data[1]),
                    'email'          => trim($data[2]),
                    'password'       => Hash::make(Str::random(16)),
                    'must_change_password' => true,
                ]);

                $roleName = strtolower(trim($data[3]));
                $role = \App\Models\Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }

                $token = Password::broker()->createToken($user);
                Mail::to($user->email)->queue(new WelcomeUserMail($user, $token));
                
                $results['imported']++;
            });
        }
        fclose($handle);

        return redirect()->route('admin.users.index')
            ->with('success', "Import complete! Imported: {$results['imported']}, Skipped: {$results['skipped']}.")
            ->withErrors($results['errors']);
    }

    public function showImportForm()
    {
        return view('admin.users.import');
    }
}