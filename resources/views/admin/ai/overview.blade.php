@extends('layouts.admin')

@section('title', 'AI Overview')

@section('content')
<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="bi bi-stars text-brand-red"></i> AI Overview
        </h1>
        <p class="text-sm text-gray-500 mt-1">Sales, stock, customers, and category insights — grounded in real data.</p>
    </div>

    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm text-gray-500 font-medium">Period:</label>
        <select
            name="period"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
            onchange="this.form.submit()"
        >
            @php
                $options = [
                    'today'       => 'Today',
                    'yesterday'   => 'Yesterday',
                    'this_week'   => 'This Week',
                    'last_week'   => 'Last Week',
                    'last_7_days' => 'Last 7 Days',
                    'last_30_days'=> 'Last 30 Days',
                    'this_month'  => 'This Month',
                    'last_month'  => 'Last Month',
                    'this_year'   => 'This Year',
                ];
            @endphp
            @foreach($options as $key => $label)
                <option value="{{ $key }}" @selected(($period ?? 'last_30_days') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <a href="{{ route('admin.ai.predictions') }}?period={{ $period ?? 'last_30_days' }}"
           class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm shadow-brand-red/20">
            <i class="bi bi-graph-up-arrow"></i> Predictions
        </a>
    </form>
</div>

{{-- Error --}}
@isset($error)
    <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-lg shrink-0"></i>
        <div>
            <p class="font-semibold">AI service unavailable</p>
            <p class="text-sm mt-0.5">{{ $error }} Make sure the Python AI service is running on port 8001.</p>
        </div>
    </div>
@endisset

@if(!$overview)
    {{-- Skeleton / No data --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @for($i = 0; $i < 3; $i++)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 animate-pulse">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100"></div>
                    <div class="h-4 bg-gray-100 rounded w-28"></div>
                </div>
                <div class="h-7 bg-gray-100 rounded w-20 mb-2"></div>
                <div class="h-3 bg-gray-100 rounded w-36"></div>
            </div>
        @endfor
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center text-gray-400">
        <i class="bi bi-stars text-4xl opacity-30 block mb-2"></i>
        <p class="font-semibold">AI overview not available</p>
        <p class="text-sm mt-1">Start the AI service and try again.</p>
    </div>
@else
    @php
        $cards           = $overview['cards'] ?? [];
        $recommendations = $overview['recommendations'] ?? [];
        $topProducts     = $overview['top_products'] ?? [];
        $stockAlerts     = $overview['stock_alerts'] ?? [];
    @endphp

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ max(count($cards), 1) > 3 ? '4' : '3' }} gap-4 mb-6">
        @foreach($cards as $card)
            @php
                $dir   = $card['change_direction'] ?? 'flat';
                $arrow = $dir === 'up' ? 'bi-arrow-up-short' : ($dir === 'down' ? 'bi-arrow-down-short' : 'bi-dash');
                $changeCls = $dir === 'up'   ? 'bg-green-100 text-green-700'
                           : ($dir === 'down' ? 'bg-red-100 text-red-700'
                           : 'bg-gray-100 text-gray-600');
                $iconName = $card['icon'] ?? 'bi-stars';
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 group-hover:bg-red-50 flex items-center justify-center transition-colors">
                        <i class="bi {{ $iconName }} text-brand-red text-lg"></i>
                    </div>
                    @if(!empty($card['change']))
                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-bold {{ $changeCls }}">
                            <i class="bi {{ $arrow }} text-sm"></i>{{ $card['change'] }}
                        </span>
                    @endif
                </div>
                <p class="text-2xl font-black text-gray-900 mb-1">{{ $card['value'] ?? '—' }}</p>
                <p class="text-sm font-semibold text-gray-600">{{ $card['title'] ?? '' }}</p>
                @if(!empty($card['detail']))
                    <p class="text-xs text-gray-400 mt-1">{{ $card['detail'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Bottom grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recommendations --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-lightbulb-fill text-brand-red"></i> AI Recommendations
                </h2>
                <span class="text-xs text-gray-400 font-medium">{{ ucwords(str_replace('_', ' ', $period ?? 'last_30_days')) }}</span>
            </div>
            @if(empty($recommendations))
                <p class="text-sm text-gray-400 text-center py-6">No recommendations for this period.</p>
            @else
                <ul class="space-y-3">
                    @foreach($recommendations as $idx => $rec)
                        <li class="flex items-start gap-3 p-3 rounded-xl {{ $idx % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} border border-gray-50">
                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="bi bi-check-lg text-green-600 text-xs font-bold"></i>
                            </div>
                            <span class="text-sm text-gray-700">{{ $rec }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="space-y-4">
            {{-- Top Products snippet --}}
            @if(!empty($topProducts))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 mb-3">
                        <i class="bi bi-trophy-fill text-brand-red text-sm"></i> Top Products
                    </h3>
                    <div class="space-y-2">
                        @foreach(array_slice($topProducts, 0, 4) as $idx => $p)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[11px] font-black text-gray-400 w-4 shrink-0">{{ $idx + 1 }}</span>
                                    <span class="text-xs text-gray-700 truncate">{{ $p['name'] ?? $p['product'] ?? '—' }}</span>
                                </div>
                                <span class="text-xs font-bold text-gray-500 shrink-0">{{ $p['quantity'] ?? $p['total_quantity'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Stock Alerts snippet --}}
            @if(!empty($stockAlerts))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-amber-500 text-sm"></i> Stock Alerts
                    </h3>
                    <div class="space-y-2">
                        @foreach(array_slice($stockAlerts, 0, 4) as $item)
                            @php
                                $lvl = $item['risk_level'] ?? ($item['stock'] < 5 ? 'critical' : 'warning');
                            @endphp
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-gray-700 truncate">{{ $item['name'] ?? $item['product'] ?? '—' }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $lvl === 'critical' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $item['stock'] ?? '' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- AI Chat shortcut --}}
            <a href="{{ route('admin.ai.chat') }}" class="block bg-brand-red text-white rounded-2xl p-4 hover:bg-red-700 transition-colors group">
                <div class="flex items-center gap-3">
                    <i class="bi bi-chat-left-dots-fill text-2xl text-red-200"></i>
                    <div>
                        <p class="font-bold text-sm">Ask AI Assistant</p>
                        <p class="text-xs text-red-200 mt-0.5">Natural language business queries</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endif
@endsection
