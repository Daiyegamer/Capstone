@extends('layouts.app')

@section('content')
    <div class="container text-center mt-5">
        <h1 class="display-4">Welcome to Mosque Finder</h1>
        <p class="lead">Quickly find nearby mosques with directions, distance, and more.</p>
        <!-- Find Mosques Button -->
        <button id="findMosquesBtn" class="btn btn-success mt-3">Find Mosques Near Me</button>

    </div>
    <div class="d-flex justify-content-center mt-3">
        <div id="map" style="height: 200px; width: 50%;"></div>
    </div>

    <ul id="mosqueList" class="list-group mt-4 container"></ul>

@endsection
<script>
    let map;
    let service;
    let infowindow;

    function initMap() {
        const defaultLocation = { lat: 43.6532, lng: -79.3832 };

        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultLocation,
            zoom: 13,
        });

        document.getElementById("findMosquesBtn").addEventListener("click", () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(userLocation);

                    const request = {
                        location: userLocation,
                        rankBy: google.maps.places.RankBy.DISTANCE,
                        keyword: 'mosque masjid' // ← multiple keywords allowed as a space-separated string
                    };
                    service = new google.maps.places.PlacesService(map);
                    service.nearbySearch(request, (results, status) => {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            const topMosques = results.slice(0, 3);
                            listMosquesWithDirections(topMosques, userLocation);
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
    const list = document.getElementById("mosqueList");
    list.innerHTML = "";

    const directionsService = new google.maps.DirectionsService();
    const resultsWithTime = [];
    let completed = 0;

    mosques.forEach((mosque, index) => {
        const request = {
            origin: userLocation,
            destination: { placeId: mosque.place_id },
            travelMode: 'DRIVING'
        };

        directionsService.route(request, (result, status) => {
            if (status === 'OK' && result.routes[0]) {
                const leg = result.routes[0].legs[0];
                resultsWithTime.push({
                    name: mosque.name,
                    website: mosque.website || '',
                    placeId: mosque.place_id,
                    durationText: leg.duration.text,
                    durationValue: leg.duration.value, // used for sorting
                    distance: leg.distance.text
                });
            }

            completed++;
            if (completed === mosques.length) {
                // All requests are done, sort and display
                resultsWithTime.sort((a, b) => a.durationValue - b.durationValue);
                const topThree = resultsWithTime.slice(0, 3);
                topThree.forEach(m => {
                    const li = document.createElement("li");
                    li.className = "list-group-item";
                    li.innerHTML = `
                        <strong>${m.name}</strong><br/>
                        Distance: ${m.distance}<br/>
                        ETA: ${m.durationText}<br/>
                        <a href="https://www.google.com/maps/dir/?api=1&destination_place_id=${m.placeId}&travelmode=driving" target="_blank">Directions</a>
                        ${m.website ? `<br/><a href="${m.website}" target="_blank">Website</a>` : ""}
                    `;
                    list.appendChild(li);
                });
            }
        });
    });
}

   
</script>