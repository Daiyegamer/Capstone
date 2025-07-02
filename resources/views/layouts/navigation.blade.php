<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container position-relative">
        <a class="navbar-brand position-absolute start-50 translate-middle-x" href="{{ url('/') }}">Mosque Finder</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="ms-auto d-flex align-items-center">
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
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