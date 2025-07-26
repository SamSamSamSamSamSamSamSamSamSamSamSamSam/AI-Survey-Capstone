@props(['activeLink' => null, 'menuItems' => []])
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen bg-gray-800 text-white p-4
                           transform -translate-x-full transition-transform duration-300 ease-in-out
                           lg:translate-x-0 lg:static lg:h-auto lg:shadow-none rounded-lg shadow-lg">
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
            {{ $slot }} <!-- This is for dynamic content -->
        </ul>
    </nav>
</aside>