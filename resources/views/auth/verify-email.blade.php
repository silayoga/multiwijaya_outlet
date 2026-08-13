@extends('layouts.app')

@section('title', 'Verify your email')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Verify your email</h1>
    <p class="sub">We sent a verification link to <strong>{{ Auth::user()->email }}</strong>. Click it to activate your account.</p>

    @if (session('status') === 'verification-link-sent')
      <div class="status-msg">A new verification link has been sent.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="btn btn-primary">Resend verification email</button>
    </form>

    <p class="auth-foot">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-link">Log out</button>
      </form>
    </p>
  </div>
</div>
@endsection
