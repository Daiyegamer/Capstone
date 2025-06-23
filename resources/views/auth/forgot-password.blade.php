@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h2 class="mb-4">Forgot Password</h2>

      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
          @error('email')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
      </form>
    </div>
  </div>
</div>
@endsection
