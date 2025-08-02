@props(['activeLink' => null, 'menuItems' => []])
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen bg-gray-800 text-white p-4
                           transform -translate-x-full transition-transform duration-300 ease-in-out">
    <!-- Exit button for mobile -->
    <button id="sidebar-exit" class="block absolute top-4 right-4 p-2 rounded-full bg-gray-700 hover:bg-gray-600 focus:outline-none" aria-label="Close sidebar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
    <nav>
        <h2 class="text-2xl font-semibold mb-6 text-indigo-300">Dashboard</h2>
        <ul class="space-y-3">
            @foreach($menuItems as $item)
                <li>
                    @if (isset($item['type']) && $item['type'] === 'logout')
                        {{-- Render as a form for logout --}}
                        <form method="POST" action="{{ $item['url'] }}" class="inline-block w-full" id="logout-form-{{ $loop->index }}">
                            @csrf
                            <a href="#"
                               onclick="event.preventDefault(); document.getElementById('logout-form-{{ $loop->index }}').submit();"
                               class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-700 transition duration-200"
                            >
                                {!! $item['icon'] !!}
                                <span>{{ $item['name'] }}</span>
                            </a>
                        </form>
                    @else
                        {{-- Render as a regular link --}}
                        <a href="{{ $item['url'] }}" class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-700 transition duration-200 {{ request()->routeIs($item['route_name']) ? 'bg-gray-700' : '' }}">
                            {!! $item['icon'] !!}
                            <span>{{ $item['name'] }}</span>
                        </a>
                    @endif
                </li>
            @endforeach
            {{ $slot }} </ul>
    </nav>
</aside>