@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Reset your password</h1>
    <p class="sub">Enter your email and we'll send you a password reset link.</p>

    @if (session('status'))
      <div class="status-msg">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf

      <div class="field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="btn btn-primary">Email password reset link</button>
    </form>

    <p class="auth-foot"><a href="{{ route('login') }}">Back to log in</a></p>
  </div>
</div>
@endsection
