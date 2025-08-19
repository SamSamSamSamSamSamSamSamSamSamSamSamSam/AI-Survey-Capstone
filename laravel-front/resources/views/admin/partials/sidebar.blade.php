@php
    $user = auth()->user();
    $role = strtolower($user->role ?? 'guest');
@endphp

<aside class="w-64 bg-white shadow rounded p-4 mb-6">
    <div class="mb-6">
        <div class="text-lg font-semibold text-gray-800">{{ $user->name ?? 'Guest' }}</div>
        <div class="text-sm text-gray-500">{{ ucfirst($role) }}</div>
    </div>

    <nav class="space-y-2">
        {{-- Common link --}}
        <a href="{{ route('admin.dashboard') ?? url('/') }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Dashboard</a>

        <!-- @if($role === 'admin')
            <a href="{{ route('admin.users.index') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Users</a>
            <a href="{{ route('admin.reports.index') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Reports</a>
            <a href="{{ route('admin.settings') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Settings</a>
        @elseif($role === 'teacher')
            <a href="{{ route('teacher.classes') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">My Classes</a>
            <a href="{{ route('teacher.assignments') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Assignments</a>
            <a href="{{ route('teacher.gradebook') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Gradebook</a>
        @elseif($role === 'student')
            <a href="{{ route('student.courses') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Courses</a>
            <a href="{{ route('student.assignments') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Assignments</a>
            <a href="{{ route('student.profile') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Profile</a>
        @else
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Home</a>
            <a href="{{ route('profile') ?? '#' }}" class="block px-3 py-2 rounded text-gray-700 hover:bg-gray-100">Profile</a>
        @endif -->
    </nav>

    <div class="mt-6">
        {{-- Delegate to the reusable sidebar component --}}
        <x-sidebar />
    </div>
</aside>
    </div>
</aside>
