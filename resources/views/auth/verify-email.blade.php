@extends('layouts.app')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 text-center">
      <h2 class="mb-4">Verify Your Email</h2>

      @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">
          A new verification link has been sent to your email address.
        </div>
      @endif

      <p>Before proceeding, please check your email for a verification link.</p>
      <p>If you did not receive the email, click below to request another.</p>

      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
      </form>
    </div>
  </div>
</div>
@endsection
