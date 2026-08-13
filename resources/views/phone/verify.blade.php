@extends('layouts.app')

@section('title', 'Verify your phone')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">

    @if ($user->phone_verified_at)

      <h1>Phone verified</h1>
      <p class="sub">{{ $user->phone_number }} is verified. You're all set to check out.</p>
      <a href="{{ route('catalog.index') }}" class="btn btn-primary">Continue shopping</a>

    @else

      @if (session('status'))
        <div class="status-msg">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="error-msg">{{ session('error') }}</div>
      @endif

      @if ($pendingOtp)

        <h1>Enter the code</h1>
        <p class="sub">We sent a 6-digit code to <strong>{{ $user->phone_number }}</strong> on WhatsApp. It expires in 5 minutes.</p>

        <form method="POST" action="{{ route('phone.verify-otp') }}">
          @csrf
          <div class="field">
            <label for="code">Verification code</label>
            <input id="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" name="code" required autofocus
                   autocomplete="one-time-code" placeholder="123456">
            @error('code')<p class="field-error">{{ $message }}</p>@enderror
          </div>
          <button type="submit" class="btn btn-primary">Verify</button>
        </form>

        <form method="POST" action="{{ route('phone.send-otp') }}" class="mt-4">
          @csrf
          <input type="hidden" name="phone_number" value="{{ $user->phone_number }}">
          <button type="submit" id="resend-btn" class="btn btn-outline" disabled>
            Resend code <span id="resend-countdown"></span>
          </button>
        </form>

        <script>
          (function () {
            var sentAt = new Date('{{ $pendingOtp->created_at->toIso8601String() }}').getTime();
            var cooldownMs = 60 * 1000;
            var btn = document.getElementById('resend-btn');
            var label = document.getElementById('resend-countdown');

            function tick() {
              var remaining = Math.ceil((sentAt + cooldownMs - Date.now()) / 1000);
              if (remaining <= 0) {
                btn.disabled = false;
                label.textContent = '';
                clearInterval(timer);
              } else {
                label.textContent = '(' + remaining + 's)';
              }
            }

            tick();
            var timer = setInterval(tick, 1000);
          })();
        </script>

      @else

        <h1>Verify your phone</h1>
        <p class="sub">Add a WhatsApp-reachable phone number. We'll send you a 6-digit code to confirm it before you can place an order.</p>

        <form method="POST" action="{{ route('phone.send-otp') }}">
          @csrf
          <div class="field">
            <label for="phone_number">Phone number</label>
            <input id="phone_number" type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                   required autofocus placeholder="08123456789" inputmode="tel">
            @error('phone_number')<p class="field-error">{{ $message }}</p>@enderror
          </div>
          <button type="submit" class="btn btn-primary">Send code</button>
        </form>

      @endif

    @endif

  </div>
</div>
@endsection
