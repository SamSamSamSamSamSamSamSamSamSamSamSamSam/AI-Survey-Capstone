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

        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $requiredHeaders = ['user_id_number', 'name', 'email', 'role'];
        if (!$header || count(array_intersect($requiredHeaders, array_map('strtolower', $header))) < 4) {
            fclose($handle);
            return back()->withErrors([
                'csv_file' => 'Invalid CSV format. Please ensure your file has headers: ' . implode(', ', $requiredHeaders),
            ]);
        }

        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        while (($data = fgetcsv($handle)) !== false) {
            if (empty($data[0]) || empty($data[2])) {
                $results['errors'][] = 'Skipped row: Missing required fields.';
                $results['skipped']++;
                continue;
            }

            $exists = User::where('user_id_number', trim($data[0]))
                ->orWhere('email', trim($data[2]))
                ->exists();

            if ($exists) {
                $results['errors'][] = "Skipped duplicate: {$data[2]} ({$data[0]}) already exists.";
                $results['skipped']++;
                continue;
            }

            DB::transaction(function () use ($data, &$results) {
                $user = User::create([
                    'user_id_number'       => trim($data[0]),
                    'name'                 => trim($data[1]),
                    'email'                => trim($data[2]),
                    'password'             => Hash::make(Str::random(16)),
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

        $message   = "Import complete! Imported: {$results['imported']}, Skipped: {$results['skipped']}.";
        $redirect  = redirect()->route('admin.users.index');
        $hasIssues = $results['skipped'] > 0 || !empty($results['errors']);

        // Nothing got through and there were skips/errors → danger
        if ($results['imported'] === 0 && $hasIssues) {
            return $redirect
                ->with('error', $message)
                ->withErrors($results['errors']);
        }

        // Some imported but not all rows were clean → warning
        if ($results['imported'] > 0 && $hasIssues) {
            return $redirect
                ->with('warning', $message)
                ->withErrors($results['errors']);
        }

        // Every row imported successfully
        return $redirect->with('success', $message);
    }

    public function showImportForm()
    {
        return view('admin.users.import');
    }
}