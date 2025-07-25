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
<script>
    let map;
    let service;
    let infowindow;
    let savedFavorites = [];

 function fetchSavedFavorites() {
    if (!isLoggedIn)
 {
        savedFavorites = []; // skip fetch and continue
        return Promise.resolve();
    }

    return fetch('/favorites')
        .then(res => res.json())
        .then(data => {
            savedFavorites = data.map(fav => fav.place_id);
        })
        .catch(() => {
            savedFavorites = []; // fallback in case of error
        });
}



    function initMap() {
        const defaultLocation = { lat: 43.6532, lng: -79.3832 };

        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultLocation,
            zoom: 13,
        });

        document.getElementById("findMosquesBtn").addEventListener("click", () => {
      document.getElementById("loadingSpinner").classList.remove("d-none");
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(userLocation);

                    new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: "Your Location",
                        icon: {
                            url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                        }
                    });

                    const request = {
                        location: userLocation,
                        rankBy: google.maps.places.RankBy.DISTANCE,
                        keyword: 'mosque masjid' // ← multiple keywords allowed as a space-separated string
                    };
                    service = new google.maps.places.PlacesService(map);
                    service.nearbySearch(request, (results, status) => {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            const topMosques = results.slice(0, 3);
                            fetchSavedFavorites().then(() => {
                                listMosquesWithDirections(topMosques, userLocation).then(() => {
                            document.getElementById("loadingSpinner").classList.add("d-none"); 
                        });
                    });
                } else {
                    document.getElementById("loadingSpinner").classList.add("d-none");
                }
            });
                }, () => {
                    alert("Geolocation failed or denied.");
                });
            } else {
                alert("Geolocation is not supported by your browser.");
            }
        });
    }
    function listMosquesWithDirections(mosques, userLocation) {
    return new Promise((resolve) => {
        const list = document.getElementById("mosqueList");
        list.innerHTML = "";

        const directionsService = new google.maps.DirectionsService();
        const placeService = new google.maps.places.PlacesService(map);
        const resultsWithDetails = [];
        let completed = 0;
if (window.mosqueMarkers) {
    window.mosqueMarkers.forEach(m => m.setMap(null));
}
window.mosqueMarkers = [];
        mosques.forEach((mosque, index) => {
            const detailsRequest = {
                placeId: mosque.place_id,
               fields: ['name', 'website', 'formatted_address', 'rating', 'formatted_phone_number']
                
                
            };

            placeService.getDetails(detailsRequest, (detailsResult, detailsStatus) => {
                const name = detailsResult?.name || mosque.name;
                const website = detailsResult?.website || '';
                const address = detailsResult?.formatted_address || '';
                const rating  = detailsResult?.rating || null;
                const phone = detailsResult?.formatted_phone_number || '';
                const directionsRequest = {
                    origin: userLocation,
                    destination: { placeId: mosque.place_id },
                    travelMode: 'DRIVING'
                };

                directionsService.route(directionsRequest, (routeResult, routeStatus) => {
                    if (routeStatus === 'OK' && routeResult.routes[0]) {
                        const leg = routeResult.routes[0].legs[0];
                        resultsWithDetails.push({
                            name,
                            phone,
                            website,
                            address,
                            rating,
                            durationText: leg.duration.text,
                            durationValue: leg.duration.value,
                            distance: leg.distance.text,
                            placeId: mosque.place_id,
                            location: leg.end_location
                        });

                   const marker = new google.maps.Marker({
    map: map,
    position: leg.end_location,
    icon: {
        url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png",
        scaledSize: new google.maps.Size(40, 40)
    },
    label: {
        text: name,
        fontWeight: 'bold',
        fontSize: '12px',
        color: 'black'
    }
});
window.mosqueMarkers.push(marker);
                    }

                    completed++;
                    if (completed === mosques.length) {
                        resultsWithDetails.sort((a, b) => a.durationValue - b.durationValue);
                        const topThree = resultsWithDetails.slice(0, 3);

                        topThree.forEach(m => {
                            const li = document.createElement("li");
                            li.className = "list-group-item";

                            const directionsLink = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(m.address)}&travelmode=driving`;

                            li.innerHTML = `
                                            <strong>${m.name}  </strong>📞 <a href="tel:${phone}">${phone}</a><br/>
                                            Distance: ${m.distance}<br/>
                                            ETA: ${m.durationText}<br/>
                                            <a href="${directionsLink}" target="_blank">Directions</a>
                                            ${m.website ? `<br/><a href="${m.website}" target="_blank">Website</a>` : ""}<br/>
                                            ${m.rating ? `⭐ ${m.rating.toFixed(1)}/ 5` : ""}<br/>
${(isLoggedIn && !savedFavorites.includes(m.placeId)) ? `
    <button class="btn btn-outline-danger btn-sm mt-2 save-fav-btn" 
        data-name="${m.name}" 
        data-place-id="${m.placeId}">
        ❤️ Save to Favorites
    </button>
` : (isLoggedIn ? `
    <span class="badge bg-success mt-2">✅ Saved as Favorite</span>
` : '')}



                                        `;
                            list.appendChild(li);
                            
                        });
                        resolve();
                    }
                   });
                });
            });
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('save-fav-btn')) {
            const name = e.target.getAttribute('data-name');
            const placeId = e.target.getAttribute('data-place-id');

            fetch('/favorites', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, place_id: placeId }),
            })
                .then(response => response.json())
                .then(data => {
                    const msgDiv = document.getElementById('favMessage');
                    e.target.outerHTML = `<span class="badge bg-success mt-2">✅ Saved as Favorite</span>`;

                    // Show success message
                    msgDiv.textContent = data.message;
                    msgDiv.classList.remove('d-none');
                    msgDiv.classList.add('show');

                    // Automatically hide after 5 seconds
                    setTimeout(() => {
                        msgDiv.classList.add('d-none');
                    }, 5000);
                })
                .catch(error => {
                    const msgDiv = document.getElementById('favMessage');
                    msgDiv.textContent = 'Error saving favorite.';
                    msgDiv.classList.remove('d-none');
                    msgDiv.classList.remove('alert-success');
                    msgDiv.classList.add('alert-danger');

                    setTimeout(() => {
                        msgDiv.classList.add('d-none');
                        msgDiv.classList.remove('alert-danger');
                        msgDiv.classList.add('alert-success');
                    }, 5000);
                });
        }
    });


</script>

<script>
    function toggleFavoritesDropdown() {
        const dropdown = document.getElementById('favoritesDropdown');
        const isVisible = !dropdown.classList.contains('d-none');

        if (isVisible) {
            dropdown.classList.add('d-none');
        } else {
            dropdown.classList.remove('d-none');
            loadFavorites(); // ← this is the missing link!
        }
    }
</script>