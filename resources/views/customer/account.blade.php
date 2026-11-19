
@extends('layouts.app')

@section('title', 'My Account')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="fw-bold mb-2">My Account</h2>
            <p class="text-muted">Your customer account details.</p>

            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-3">
                <strong>Name</strong>
                <div>{{ auth()->user()->name }}</div>
            </div>

            <div class="mb-3">
                <strong>Email</strong>
                <div>{{ auth()->user()->email }}</div>
            </div>

            <div class="mb-4">
                <strong>Email verification</strong>
                <div class="text-success">Verified</div>
            </div>

            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button class="btn btn-dark">Sign Out</button>
            </form>
        </div>
    </div>
</div>
@endsection
