@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h2 class="mb-4">Reset Password</h2>

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
          @error('email')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">New Password</label>
          <input id="password" type="password" class="form-control" name="password" required>
          @error('password')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
      </form>
    </div>
  </div>
</div>
@endsection
