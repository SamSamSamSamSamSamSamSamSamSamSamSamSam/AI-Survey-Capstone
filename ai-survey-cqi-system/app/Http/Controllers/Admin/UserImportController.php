<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Mail\WelcomeUserMail;

class UserImportController extends Controller
{
    private const REQUIRED_HEADERS = ['user_id_number', 'name', 'email', 'role'];
    private const VALID_ROLES       = ['faculty', 'student'];

    // ──────────────────────────────────────────────────────────────
    //  Show form
    // ──────────────────────────────────────────────────────────────
    public function showImportForm()
    {
        return view('admin.users.import');
    }

    // ──────────────────────────────────────────────────────────────
    //  AJAX: preview / validate only (no writes)
    // ──────────────────────────────────────────────────────────────
    public function previewValidation(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $rows = $this->parseCsv($request);

        if ($rows === null) {
            return response()->json([
                'can_proceed'   => false,
                'valid_count'   => 0,
                'skipped_count' => 0,
                'errors'        => [['line' => 0, 'message' => 'Could not read the CSV file.']],
                'warnings'      => [],
            ]);
        }

        if (empty($rows)) {
            return response()->json([
                'can_proceed'   => false,
                'valid_count'   => 0,
                'skipped_count' => 0,
                'errors'        => [['line' => 0, 'message' => 'CSV is empty or has no data rows.']],
                'warnings'      => [],
            ]);
        }

        // Header check
        $headers = array_keys($rows[0]);
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);
        if (! empty($missing)) {
            return response()->json([
                'can_proceed'   => false,
                'valid_count'   => 0,
                'skipped_count' => 0,
                'errors'        => [[
                    'line'    => 0,
                    'message' => 'Missing required column(s): ' . implode(', ', $missing),
                ]],
                'warnings'      => [],
            ]);
        }

        $errors      = [];
        $warnings    = [];
        $validCount  = 0;
        $skipped     = 0;
        $seenInFile  = [];   // track duplicates within the CSV itself

        foreach ($rows as $i => $row) {
            $line     = $i + 2; // +1 for header, +1 for 1-based
            $idNumber = trim($row['user_id_number'] ?? '');
            $name     = trim($row['name'] ?? '');
            $email    = strtolower(trim($row['email'] ?? ''));
            $role     = strtolower(trim($row['role'] ?? ''));

            // ── Required-field checks ──────────────────────────
            if ($idNumber === '' || $email === '') {
                $errors[] = ['line' => $line, 'message' => 'Missing user_id_number or email — row skipped.'];
                $skipped++;
                continue;
            }

            if ($name === '') {
                $errors[] = ['line' => $line, 'message' => "Row for {$email}: name is empty."];
                $skipped++;
                continue;
            }

            // ── Format checks ──────────────────────────────────
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['line' => $line, 'message' => "Invalid email address: {$email}"];
                $skipped++;
                continue;
            }

            if (! in_array($role, self::VALID_ROLES, true)) {
                $errors[] = ['line' => $line, 'message' => "Unknown role \"{$role}\" for {$email} — must be 'faculty' or 'student'."];
                $skipped++;
                continue;
            }

            // ── Within-file duplicate check ────────────────────
            if (isset($seenInFile[$idNumber])) {
                $warnings[] = ['line' => $line, 'message' => "Duplicate user_id_number {$idNumber} in this file — row will be skipped."];
                $skipped++;
                continue;
            }
            if (in_array($email, $seenInFile, true)) {
                $warnings[] = ['line' => $line, 'message' => "Duplicate email {$email} in this file — row will be skipped."];
                $skipped++;
                continue;
            }
            $seenInFile[$idNumber] = $email;

            // ── DB duplicate check ─────────────────────────────
            $dbExists = User::where('user_id_number', $idNumber)
                            ->orWhere('email', $email)
                            ->exists();

            if ($dbExists) {
                $warnings[] = ['line' => $line, 'message' => "Already registered: {$email} ({$idNumber}) — will be skipped on import."];
                $skipped++;
                continue;
            }

            $validCount++;
        }

        return response()->json([
            'can_proceed'   => $validCount > 0,
            'valid_count'   => $validCount,
            'skipped_count' => $skipped,
            'errors'        => $errors,
            'warnings'      => $warnings,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  POST: perform the actual import (unchanged logic, cleaner code)
    // ──────────────────────────────────────────────────────────────
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $rows = $this->parseCsv($request);

        if ($rows === null || empty($rows)) {
            return back()->withErrors(['csv_file' => 'Could not read or parse the CSV file.']);
        }

        $headers = array_keys($rows[0]);
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);
        if (! empty($missing)) {
            return back()->withErrors([
                'csv_file' => 'Invalid CSV format. Missing columns: ' . implode(', ', $missing),
            ]);
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $row) {
            $idNumber = trim($row['user_id_number'] ?? '');
            $name     = trim($row['name'] ?? '');
            $email    = strtolower(trim($row['email'] ?? ''));
            $roleName = strtolower(trim($row['role'] ?? ''));

            if ($idNumber === '' || $email === '') {
                $errors[] = "Skipped row: missing user_id_number or email.";
                $skipped++;
                continue;
            }

            $exists = User::where('user_id_number', $idNumber)
                          ->orWhere('email', $email)
                          ->exists();

            if ($exists) {
                $errors[] = "Skipped duplicate: {$email} ({$idNumber}) already exists.";
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($idNumber, $name, $email, $roleName, &$imported) {
                $user = User::create([
                    'user_id_number'       => $idNumber,
                    'name'                 => $name,
                    'email'                => $email,
                    'password'             => Hash::make(Str::random(16)),
                    'must_change_password' => true,
                ]);

                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }

                $token = Password::broker()->createToken($user);
                Mail::to($user->email)->queue(new WelcomeUserMail($user, $token));

                $imported++;
            });
        }

        $message   = "Import complete! Imported: {$imported}, Skipped: {$skipped}.";
        $hasIssues = $skipped > 0 || ! empty($errors);

        if ($imported === 0 && $hasIssues) {
            return redirect()->route('admin.users.index')
                             ->with('error', $message)
                             ->withErrors($errors);
        }

        if ($imported > 0 && $hasIssues) {
            return redirect()->route('admin.users.index')
                             ->with('warning', $message)
                             ->withErrors($errors);
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Parse the uploaded CSV into an array of associative rows.
     * Returns null on read failure, [] on empty body.
     */
    private function parseCsv(Request $request): ?array
    {
        $path   = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return null;
        }

        $rawHeaders = fgetcsv($handle, 1000, ',');
        if (! $rawHeaders) {
            fclose($handle);
            return [];
        }

        $headers = array_map(
            fn($h) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h))),
            $rawHeaders
        );

        $rows = [];
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, array_map('trim', $data));
            }
        }

        fclose($handle);
        return $rows;
    }
}