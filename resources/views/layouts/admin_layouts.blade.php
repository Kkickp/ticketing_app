<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg flex flex-col justify-between sticky top-0 h-screen">
            <div class="p-6 flex-1 overflow-y-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Admin Panel</h2>

                <!-- Sidebar Menu -->
                <ul class="space-y-2">

                    <li>
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="w-5 h-5 mr-3">
                                <path fill="currentColor" d="M6 19h3v-5q0-.425.288-.712T10 13h4q.425 0 .713.288T15 14v5h3v-9l-6-4.5L6 10zm-2 0v-9q0-.475.213-.9t.587-.7l6-4.5q.525-.4 1.2-.4t1.2.4l6 4.5q.375.275.588.7T20 10v9q0 .825-.588 1.413T18 21h-4q-.425 0-.712-.288T13 20v-5h-2v5q0 .425-.288.713T10 21H6q-.825 0-1.412-.587T4 19m8-6.75" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.kategori.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('admin.kategori.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                            Manajemen Kategori
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.events.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('admin.events.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Manajemen Event
                        </a>
                    </li>
                </ul>
            </div>

            <!-- User Profile Section -->
            <div class="p-6 border-t bg-white">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ auth()->user()->name[0] ?? 'A' }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <a href="{{ route('profile.edit') }}" class="text-xs text-blue-600 hover:text-blue-800">
                            Profile
                        </a>
                        <span class="mx-1">•</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">
                                ← Kembali ke Home
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-3 min-w-[300px] max-w-md">
        @if(session('success'))
        <div class="alert alert-success shadow-lg transition-all duration-500 ease-in-out transform translate-y-0 opacity-100 flex items-center gap-3 p-4 rounded-xl border-none text-white bg-green-600" id="toast-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold text-sm">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error shadow-lg transition-all duration-500 ease-in-out transform translate-y-0 opacity-100 flex items-center gap-3 p-4 rounded-xl border-none text-white bg-red-600" id="toast-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold text-sm">{{ session('error') }}</span>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const successToast = document.getElementById('toast-success');
            const errorToast = document.getElementById('toast-error');

            const dismissToast = (toast) => {
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    setTimeout(() => toast.remove(), 400);
                }
            };

            if (successToast) {
                setTimeout(() => dismissToast(successToast), 4000);
            }
            if (errorToast) {
                setTimeout(() => dismissToast(errorToast), 4000);
            }
        });
    </script>
</body>

</html>