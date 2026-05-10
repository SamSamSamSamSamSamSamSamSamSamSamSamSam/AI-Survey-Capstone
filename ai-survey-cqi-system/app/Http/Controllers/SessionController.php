<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function check(Request $request) {
        // Guard: If session is dead, exit immediately with 401 without saving redirect paths
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $lastActivity = $request->session()->get('last_activity', time());
        $lifetime = config('session.lifetime');
        $elapsedMinutes = (time() - $lastActivity) / 60;
        $remaining = $lifetime - $elapsedMinutes;

        return response()->json(['minutes_remaining' => $remaining]);
    }

    public function refresh() {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Just hitting this route is enough to refresh the session
        return response()->json(['status' => 'ok']);
    }
}