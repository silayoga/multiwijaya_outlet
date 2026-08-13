@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Set a new password</h1>
    <p class="sub">Choose a new password for your account.</p>

    <form method="POST" action="{{ route('password.update') }}">
      @csrf

      <input type="hidden" name="token" value="{{ $request->route('token') }}">

      <div class="field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <div class="field">
        <label for="password">New password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <div class="field">
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary">Reset password</button>
    </form>
  </div>
</div>
@endsection
