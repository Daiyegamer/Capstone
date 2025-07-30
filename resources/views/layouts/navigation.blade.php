<nav class="navbar navbar-expand-lg bg-body-tertiary">

    <div class="container position-relative">
        <a class="navbar-brand position-absolute start-50 translate-middle-x" href="{{ url('/') }}">Mosque Finder</a>

        
        <div class="ms-auto d-flex align-items-center">
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>

                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); toggleFavoritesDropdown();">View Favorites</a></li>



                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Log Out</button>
                                </form>
                            </li>
                        </ul>

                    </li>
                @else
                    <li class="nav-item me-2">
                        <a class="btn btn-outline-primary" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth


            </ul>
        </div>
    </div>
</nav>
<!-- Favorites Dropdown (Overlay) -->
<div id="favoritesDropdown" class="card shadow-sm position-absolute end-0 me-3 mt-2 d-none" style="width: 300px; z-index: 999;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Your Favorites</strong>
        <button class="btn-close btn-sm" onclick="toggleFavoritesDropdown()"></button>
    </div>
    <ul id="favoritesList" class="list-group list-group-flush"></ul>
</div>
