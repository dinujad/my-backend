<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') – Print Works.LK Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { red: '#FF1F40', 'red-dark': '#e01a38' } } } }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        @if(!request()->has('iframe'))
        <aside class="w-64 h-screen bg-gray-900 text-white flex flex-col fixed shadow-xl z-10">
            <div class="p-5 border-b border-gray-700">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex flex-col items-start">
                    <img src="{{ asset('logo.png') }}" alt="Print Works LK" class="h-9 object-contain">
                    <span class="block text-xs text-gray-400 mt-1">Admin Panel</span>
                </a>
            </div>
            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-speedometer2 text-lg w-6 text-center"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.products.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-box-seam text-lg w-6 text-center"></i>
                    <span>Products</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.categories.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-folder2 text-lg w-6 text-center"></i>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.orders.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-cart-check text-lg w-6 text-center"></i>
                    <span>Orders</span>
                </a>
                <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.customers.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-people text-lg w-6 text-center"></i>
                    <span>Customers</span>
                </a>
                <a href="{{ route('admin.blog.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.blog.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-journal-text text-lg w-6 text-center"></i>
                    <span>Blog</span>
                </a>
                <a href="{{ route('admin.media.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.media.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-images text-lg w-6 text-center"></i>
                    <span>Media</span>
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.coupons.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-tag text-lg w-6 text-center"></i>
                    <span>Coupons</span>
                </a>
                <a href="{{ route('admin.seo.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.seo.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-search text-lg w-6 text-center"></i>
                    <span>SEO Settings</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-graph-up text-lg w-6 text-center"></i>
                    <span>Reports</span>
                </a>
                <a href="{{ route('admin.ai.overview') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.ai.overview') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-stars text-lg w-6 text-center"></i>
                    <span>AI Overview</span>
                </a>
                <a href="{{ route('admin.ai.predictions') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.ai.predictions') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-graph-up-arrow text-lg w-6 text-center"></i>
                    <span>AI Predictions</span>
                </a>
                <a href="{{ route('admin.ai.chat') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.ai.chat') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-chat-left-dots text-lg w-6 text-center"></i>
                    <span>AI Chat Assistant</span>
                </a>
                <a href="{{ route('admin.whatsapp.campaigns') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.whatsapp.*') ? 'bg-green-600 text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-whatsapp text-lg w-6 text-center"></i>
                    <span>WhatsApp Campaigns</span>
                </a>
                <a href="{{ route('admin.sms.campaigns') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.sms.*') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-chat-text-fill text-lg w-6 text-center"></i>
                    <span>SMS Campaigns</span>
                </a>
                <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.payments.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-credit-card text-lg w-6 text-center"></i>
                    <span>Payment Methods</span>
                </a>
                <a href="{{ route('admin.shipping.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.shipping.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-truck text-lg w-6 text-center"></i>
                    <span>Shipping</span>
                </a>
                <a href="{{ route('admin.quote-requests.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.quote-requests.*') ? 'bg-brand-red text-white' : 'hover:bg-gray-800 text-gray-300' }}">
                    <i class="bi bi-file-earmark-text text-lg w-6 text-center"></i>
                    <span>Quote Requests</span>
                </a>
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}/admin/live-chat" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition hover:bg-gray-800 text-gray-300">
                    <i class="bi bi-headset text-lg w-6 text-center"></i>
                    <span>Live Chat Support</span>
                </a>
            </nav>
            <div class="p-3 border-t border-gray-700">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg hover:bg-gray-800 text-gray-300 transition text-left">
                        <i class="bi bi-box-arrow-right text-lg w-6 text-center"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        @endif

        <main class="flex-1 {{ request()->has('iframe') ? '' : 'ml-64' }} p-6 lg:p-8 min-h-screen">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-green-100 text-green-800">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-100 text-red-800">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
