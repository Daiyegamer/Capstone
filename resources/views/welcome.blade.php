@extends('layouts.app')

@section('content')


<script>
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
</script>


    <div class="container text-center mt-5">
        <h1 class="display-4">Welcome to Mosque Finder</h1>
        <p class="lead">Quickly find nearby mosques with directions, distance, and more.</p>
        <!-- Find Mosques Button -->
        <button id="findMosquesBtn" class="btn btn-success mt-3">Find Mosques Near Me</button>

    </div>
    <div id="loadingSpinner" class="text-center mt-3 d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

    <div class="d-flex justify-content-center mt-3">
        <div id="map" style="height: 200px; width: 100%;"></div>
    </div>
    <div id="favMessage" class="alert alert-success d-none text-center mx-auto" style="max-width: 400px;"></div>


    <ul id="mosqueList" class="list-group mt-4 container"></ul>

@endsection
<script src="{{ asset('js/welcome.js') }}"></script>
