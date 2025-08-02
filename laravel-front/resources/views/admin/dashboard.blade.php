
@extends('layouts.dashboard') 

@section('title', 'Admin Dashboard')

@section('content')
    
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome, Admin!</h1>
    <p class="text-gray-700">This is your control panel.</p>
    <p class="text-gray-700">Welcome, {{ auth()->user()->name ?? 'Guest' }} ({{ auth()->user()->role ?? 'N/A' }})</p>

    {{-- The logout button from here has been moved into the sidebar via dynamic menus --}}

    <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Dashboard Overview</h3>
        <p class="text-gray-600">Here you can see various metrics and information related to your application.</p>
        <ul class="list-disc list-inside mt-4 text-gray-600">
            <li>Total Users: 1,234</li>
            <li>Active Products: 567</li>
            <li>Recent Orders: 89</li>
        </ul>
    </div>
@endsection