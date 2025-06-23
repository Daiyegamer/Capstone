@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h2 class="mb-4">Login</h2>

      @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
          @error('email')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
          @error('password')
            <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3 form-check">
          <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
          <label for="remember_me" class="form-check-label">Remember me</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="small">Forgot your password?</a>
          @endif
          <button type="submit" class="btn btn-primary">Log in</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
