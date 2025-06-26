@extends('layouts.app')

@section('content')
<div class="container text-center mt-5">
    <h1 class="display-4">Welcome to Mosque Finder</h1>
    <p class="lead">Quickly find nearby mosques with directions, distance, and more.</p>
    
    @if (Route::has('login'))
        <div class="mt-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @endif
            @endauth
        </div>
    @endif
</div>
@endsection
