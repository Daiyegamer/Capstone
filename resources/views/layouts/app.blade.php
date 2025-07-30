<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MosqueFinder') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            <button class="btn btn-sm btn-secondary" onclick="toggleDarkMode()">Toggle Dark Mode</button>
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



    <script>
        function toggleFavoritesDropdown() {
            const panel = document.getElementById('favoritesDropdown');
            panel.classList.toggle('d-none');

            if (!panel.classList.contains('d-none')) {
                loadFavorites();

            }
        }

        function removeFavorite(placeId) {
            fetch(`/favorites/${placeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                toggleFavoritesDropdown();
                toggleFavoritesDropdown(); // reopen to refresh
            });
        }
    </script>
    <script>
        function loadFavorites() {
            fetch('/favorites')
                .then(response => response.json())
                .then(favorites => {
                    const list = document.getElementById('favoritesList');
                    list.innerHTML = '';

                    const placeService = new google.maps.places.PlacesService(document.createElement('div'));

                    favorites.forEach(fav => {
                        const request = {
                            placeId: fav.place_id,
                            fields: ['name', 'website', 'formatted_address']
                        };

                        placeService.getDetails(request, (details, status) => {
                            if (status === google.maps.places.PlacesServiceStatus.OK) {
                                const li = document.createElement('li');
                                li.className = 'list-group-item';

                                const directionsLink = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(details.formatted_address)}&travelmode=driving`;

                                li.innerHTML = `
                                <strong>${details.name}</strong><br/>
                                <a href="${directionsLink}" target="_blank">Directions</a><br/>
                                ${details.website ? `<a href="${details.website}" target="_blank">Website</a><br/>` : ''}
                                <button class="btn btn-sm btn-danger mt-1" onclick="removeFavorite('${fav.place_id}')">Remove</button>
                            `;

                                list.appendChild(li);
                            }
                        });
                    });
                });
        }
    </script>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
        });
    </script>

    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            html.setAttribute('data-bs-theme', currentTheme === 'dark' ? 'light' : 'dark');
        }
    </script>





    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMap">
        </script>
</body>

</html>