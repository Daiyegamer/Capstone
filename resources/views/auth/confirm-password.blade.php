@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h2 class="mb-4">Confirm Password</h2>
      <p>Please confirm your password before continuing.</p>

      <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
          @error('password')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn btn-primary">Confirm</button>
      </form>
    </div>
  </div>
</div>
@endsection
