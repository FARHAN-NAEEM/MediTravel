@extends('referrals.layout')
@section('title', 'পরিবর্তনের ইতিহাস')
@section('content')
<h1>পরিবর্তনের ইতিহাস</h1>
@include('referrals.audit-table')<div class="mt-5">{{ $audits->links() }}</div>
@endsection
