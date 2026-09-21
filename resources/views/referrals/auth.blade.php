@extends('referrals.layout')
@section('title', 'এজেন্ট লগইন')
@section('content')
<div class="referral-auth">
    <h1>{{ ['login' => 'এজেন্ট লগইন', 'forgot' => 'পাসওয়ার্ড লিংক', 'reset' => 'নতুন পাসওয়ার্ড'][$mode] }}</h1>
    <form method="post" action="{{ $mode === 'login' ? route('agent.login') : ($mode === 'forgot' ? route('agent.password.email') : route('agent.password.update')) }}" class="referral-form">
        @csrf
        @if($mode === 'reset')<input type="hidden" name="token" value="{{ $token }}">@endif
        <label>ইমেইল<input type="email" name="email" required autocomplete="email" value="{{ old('email', $email ?? '') }}"></label>
        @if($mode !== 'forgot')
            <label>পাসওয়ার্ড<input type="password" name="password" required autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" @if($mode === 'reset') minlength="12" @endif></label>
        @endif
        @if($mode === 'reset')
            <label>পাসওয়ার্ড নিশ্চিত করুন<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
            <p class="referral-muted">পাসওয়ার্ড: কমপক্ষে ১২ অক্ষর, বড় ও ছোট হাতের অক্ষর, সংখ্যা এবং বিশেষ চিহ্ন।</p>
        @endif
        <button class="referral-button"><x-heroicon-o-lock-closed class="h-4 w-4" />{{ $mode === 'login' ? 'লগইন' : ($mode === 'forgot' ? 'লিংক পাঠান' : 'পাসওয়ার্ড সেট করুন') }}</button>
        <a class="referral-link" href="{{ $mode === 'login' ? route('agent.password.request') : route('agent.login') }}">{{ $mode === 'login' ? 'পাসওয়ার্ড সেট / রিসেট' : 'লগইনে ফিরুন' }}</a>
    </form>
</div>
@endsection
