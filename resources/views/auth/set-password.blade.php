@extends('layouts.app')

@section('title', 'Set password')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <h1>{{ $user->isGoogleOnly() ? 'Add a password' : 'Update your password' }}</h1>
    <p class="sub">
      @if ($user->isGoogleOnly())
        You currently sign in with Google only. Set a password to also be able to log in with {{ $user->email }} directly.
      @else
        Choose a new password for {{ $user->email }}.
      @endif
    </p>

    @if (session('status'))
      <div class="status-msg">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.set-password.store') }}">
      @csrf

      <div class="field">
        <label for="password">New password</label>
        <input id="password" type="password" name="password" required autofocus autocomplete="new-password">
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
      </div>

      <div class="field">
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary">Save password</button>
    </form>
  </div>
</div>
@endsection
