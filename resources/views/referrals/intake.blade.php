@extends('referrals.layout')
@section('title', 'চিকিৎসা সহায়তার অনুরোধ')
@section('content')
<div class="referral-narrow">
    <div class="referral-title"><div><h1>চিকিৎসা সহায়তার অনুরোধ</h1><p class="referral-muted">রেফারার: {{ $agent->business_name }} · {{ $agent->code }}</p></div></div>
    @if(session('referral_saved'))
        <div class="referral-success"><x-heroicon-o-check-circle class="h-10 w-10" /><h2>অনুরোধ জমা হয়েছে</h2><strong>{{ session('referral_saved') }}</strong>
            <a class="referral-button" href="{{ session('referral_whatsapp') }}"><x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />WhatsApp-এ যোগাযোগ করুন</a>
        </div>
    @else
        <form method="post" action="{{ $public ? route('referral.submit', $agent->code) : route('agent.store') }}" class="referral-form">@csrf
            @include('referrals.intake-fields')
            <button class="referral-button"><x-heroicon-o-paper-airplane class="h-4 w-4" />অনুরোধ পাঠান</button>
        </form>
    @endif
</div>
@endsection
