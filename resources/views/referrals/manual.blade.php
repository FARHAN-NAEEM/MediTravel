@extends('referrals.layout')
@section('title', 'ম্যানুয়াল রেফারেল')
@section('content')
<div class="referral-narrow"><h1>ম্যানুয়াল রেফারেল</h1><form method="post" action="{{ route('referral-ops.manual.store') }}" class="referral-form">@csrf
<label>এজেন্ট<select name="agent_id" required><option value="">নির্বাচন করুন</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>{{ $agent->business_name }} · {{ $agent->code }}</option>@endforeach</select></label>
@include('referrals.intake-fields')<button class="referral-button"><x-heroicon-o-plus class="h-4 w-4" />রেফারেল তৈরি করুন</button></form></div>
@endsection
