<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $user = auth()->user();
        $role = $user ? $user->role : null;
        $menuItems = [];

        if ($role === 'admin') {
            $menuItems = [
                [
                    'name' => 'Admin Dashboard',
                    'url' => route('admin.dashboard'),
                    'route_name' => 'admin.dashboard',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0h6m-6 0H7" /></svg>',
                ],
                [
                    'name' => 'Manage Users',
                    'url' => route('admin.users'),
                    'route_name' => 'admin.users',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" /></svg>',
                ],
                [
                    'name' => 'Logout',
                    'url' => route('logout'),
                    'type' => 'logout',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" /></svg>',
                ],
            ];
        } elseif ($role === 'faculty') {
            $menuItems = [
                [
                    'name' => 'Faculty Dashboard',
                    'url' => route('faculty.dashboard'),
                    'route_name' => 'faculty.dashboard',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0h6m-6 0H7" /></svg>',
                ],
                [
                    'name' => 'My Courses',
                    'url' => route('faculty.courses'),
                    'route_name' => 'faculty.courses',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" /></svg>',
                ],
                [
                    'name' => 'Logout',
                    'url' => route('logout'),
                    'type' => 'logout',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" /></svg>',
                ],
            ];
        } elseif ($role === 'student') {
            $menuItems = [
                [
                    'name' => 'Student Dashboard',
                    'url' => route('student.dashboard'),
                    'route_name' => 'student.dashboard',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7m6 0v6m0 0h6m-6 0H7" /></svg>',
                ],
                [
                    'name' => 'My Grades',
                    'url' => route('student.grades'),
                    'route_name' => 'student.grades',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" /></svg>',
                ],
                [
                    'name' => 'Logout',
                    'url' => route('logout'),
                    'type' => 'logout',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" /></svg>',
                ],
            ];
        }

        return view('components.sidebar', [
            'menuItems' => $menuItems,
        ]);
    }
}
