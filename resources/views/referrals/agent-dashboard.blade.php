@extends('referrals.layout')
@section('title', 'আমার রেফারেল')
@section('content')
<div class="referral-title"><div><h1>আমার রেফারেল</h1><p class="referral-muted">{{ $agent->business_name }} · {{ $agent->code }}</p></div><a class="referral-button" href="{{ route('agent.create') }}"><x-heroicon-o-plus class="h-5 w-5" />নতুন রেফারেল</a></div>
<dl class="referral-metrics"><div><dt>জমা দেওয়া কেস</dt><dd>{{ $counts['submitted'] }}</dd></div><div><dt>যাচাইকৃত কেস</dt><dd>{{ $counts['verified'] }}</dd></div><div><dt>সেবা সম্পন্ন</dt><dd>{{ $counts['completed'] }}</dd></div></dl>
<section class="referral-share">
    <div><h2>আমার রেফারেল লিংক</h2><div class="referral-copy" x-data="{ copied: false }"><input aria-label="রেফারেল লিংক" readonly value="{{ route('referral.public', $agent->code) }}" x-ref="link"><button class="referral-icon" title="লিংক কপি করুন" aria-label="লিংক কপি করুন" @click="navigator.clipboard.writeText($refs.link.value).then(() => copied = true).catch(() => $refs.link.select())"><x-heroicon-o-clipboard-document class="h-5 w-5" /></button><span x-show="copied" x-cloak role="status">কপি হয়েছে</span></div></div>
    <div class="referral-qr"><canvas width="160" height="160" data-referral-qr="{{ route('referral.public', $agent->code) }}" aria-label="রেফারেল QR কোড"></canvas><a data-qr-download download="{{ $agent->code }}.png" class="referral-link" hidden><x-heroicon-o-arrow-down-tray class="h-4 w-4" />QR ডাউনলোড</a></div>
</section>
<div class="referral-title"><h2>কেস ও কমিশন</h2><a href="{{ route('agent.statement') }}" class="referral-link"><x-heroicon-o-arrow-down-tray class="h-4 w-4" />স্টেটমেন্ট</a></div>
<form class="referral-filter" method="get"><label>অবস্থা<select name="status"><option value="">সব অবস্থা</option>@foreach(\App\Models\ReferralCase::statuses() as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label><button class="referral-button secondary"><x-heroicon-o-funnel class="h-4 w-4" />ফিল্টার</button></form>
<div class="referral-table-wrap"><table class="referral-table"><thead><tr><th>রেফারেল</th><th>রোগী</th><th>অবস্থা</th><th>হার</th><th>সম্ভাব্য হিসাব</th><th>অনুমোদিত</th><th>পরিশোধিত</th><th>অপরিশোধিত</th></tr></thead><tbody>
@forelse($cases as $case)
@php($b = $balances[$case->id])
<tr><td><a class="referral-link" href="{{ route('agent.show', $case->reference) }}">{{ $case->reference }}</a><small>{{ $case->created_at->format('d M Y') }}</small></td><td>{{ $case->patient->name }}<small>{{ substr($case->patient->phone, 0, 4) }}******{{ substr($case->patient->phone, -3) }}</small></td><td><span class="referral-badge">{{ \App\Models\ReferralCase::statuses()[$case->status] }}</span></td><td>{{ $case->commission_rate_bps === null ? 'নির্ধারিত নয়' : \App\Support\ReferralMoney::format($case->commission_rate_bps).'%' }}@if($case->commission_rate_bps !== null && ! $case->rate_acknowledged_at)<small class="referral-pending">সম্মতি অপেক্ষমাণ</small>@endif</td><td>৳{{ \App\Support\ReferralMoney::format($b['calculated']) }}</td><td>৳{{ \App\Support\ReferralMoney::format($b['approved']) }}</td><td>৳{{ \App\Support\ReferralMoney::format($b['paid']) }}</td><td>৳{{ \App\Support\ReferralMoney::format($b['payable']) }}</td></tr>
@empty<tr><td colspan="8" class="referral-empty">এখনো কোনো রেফারেল নেই।</td></tr>@endforelse
</tbody></table></div>
<div class="mt-5">{{ $cases->links() }}</div>
@endsection
