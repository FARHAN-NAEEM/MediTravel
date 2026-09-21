<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'রেফারেল নেটওয়ার্ক') | Asian Health Connect</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="referral-app">
@php($operations = request()->routeIs('referral-ops.*'))
<header class="referral-header">
    <a href="{{ $operations ? route('referral-ops.index') : route('agent.dashboard') }}" aria-label="Asian Health Connect"><img src="{{ asset('images/asian-health-connect-logo.png') }}" alt="Asian Health Connect" width="210" height="70"></a>
    <div class="referral-account">
        @if($operations)
            <span>{{ auth()->user()->name }}</span><a href="{{ route('filament.admin.pages.dashboard') }}" class="referral-link"><x-heroicon-o-arrow-left class="h-4 w-4" /> অ্যাডমিন</a>
        @elseif(auth('agent')->check())
            <span>{{ auth('agent')->user()->name }}</span>
            <form method="post" action="{{ route('agent.logout') }}">@csrf<button class="referral-link" title="লগআউট"><x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" /><span>লগআউট</span></button></form>
        @else
            <a href="{{ route('home') }}" class="referral-link">ওয়েবসাইট</a>
        @endif
    </div>
</header>
@if($operations)
<nav class="referral-nav" aria-label="রেফারেল ব্যবস্থাপনা">
    <a href="{{ route('referral-ops.index') }}" @if(request()->routeIs('referral-ops.index','referral-ops.show')) aria-current="page" @endif>কেসসমূহ</a>
    @if(auth()->user()->isOwner())
        <a href="{{ route('referral-ops.agents') }}" @if(request()->routeIs('referral-ops.agents')) aria-current="page" @endif>এজেন্ট</a>
        <a href="{{ route('referral-ops.rules') }}" @if(request()->routeIs('referral-ops.rules')) aria-current="page" @endif>কমিশন ও চুক্তি</a>
        <a href="{{ route('referral-ops.audit') }}" @if(request()->routeIs('referral-ops.audit')) aria-current="page" @endif>পরিবর্তনের ইতিহাস</a>
    @endif
</nav>
@elseif(auth('agent')->check())
<nav class="referral-nav" aria-label="এজেন্ট পোর্টাল">
    <a href="{{ route('agent.dashboard') }}" @if(request()->routeIs('agent.dashboard')) aria-current="page" @endif>আমার রেফারেল</a>
    <a href="{{ route('agent.create') }}" @if(request()->routeIs('agent.create')) aria-current="page" @endif>নতুন রেফারেল</a>
</nav>
@endif
<main class="referral-main">
    @if(session('success'))<div class="referral-notice" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="referral-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="referral-footer">Asian Health Connect · {{ now()->year }}</footer>
</body>
</html>
