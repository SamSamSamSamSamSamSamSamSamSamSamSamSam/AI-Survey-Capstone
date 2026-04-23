<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function check(Request $request) {
        // Laravel sessions expire based on 'last_activity'.
        // We calculate how many minutes until that hits session.lifetime.
        $lastActivity = $request->session()->get('last_activity', time());
        $lifetime = config('session.lifetime');
        $elapsedMinutes = (time() - $lastActivity) / 60;
        $remaining = $lifetime - $elapsedMinutes;

        return response()->json(['minutes_remaining' => $remaining]);
    }

    public function refresh() {
        // Just hitting this route is enough to refresh the session
        return response()->json(['status' => 'ok']);
    }
}
