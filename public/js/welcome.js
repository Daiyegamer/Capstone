
let map;  
let service;  
let infowindow;  
let savedFavorites = [];  
function checkDistance(coord1, coord2) {  
    const R = 6371; // Earth radius in km  
    const dLat = toRad(coord2.lat - coord1.lat);  
    const dLng = toRad(coord2.lng - coord1.lng);  
    const lat1 = toRad(coord1.lat);  
    const lat2 = toRad(coord2.lat);  
  
    const a = Math.sin(dLat / 2) ** 2 +  
        Math.cos(lat1) * Math.cos(lat2) *  
        Math.sin(dLng / 2) ** 2;  
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));  
  
    return R * c;  
}  
  
function toRad(x) {  
    return x * Math.PI / 180;  
}  
  
const CACHE_TIMEOUT = 120 * 60 * 1000;  
  
function roundCoord(coord) {  
    return {  
        lat: Math.round(coord.lat * 10000) / 10000,  
        lng: Math.round(coord.lng * 10000) / 10000  
    };  
}  
  
  
function getCachedMosques(currentLocation) {  
    const cache = localStorage.getItem('mosqueCache');  
    if (!cache) return null;  
  
    const data = JSON.parse(cache);  
    const timeDiff = Date.now() - data.timestamp;  
    const isNearby = checkDistance(roundCoord(currentLocation), roundCoord(data.userLocation)) < 1;  
  
   if (timeDiff < CACHE_TIMEOUT && isNearby) {
    console.log("✅ Using cached mosque data.");

    // ✅ Always restore savedFavorites into global memory
    savedFavorites = data.savedFavorites || [];

    return {
        mosques: data.mosques,
        savedFavorites: savedFavorites
    };
}

  
    return null;  
}  
  
  
function fetchSavedFavorites() {
    if (!isLoggedIn) {
        const cache = localStorage.getItem('mosqueCache');
        savedFavorites = cache ? JSON.parse(cache).savedFavorites || [] : [];
        return Promise.resolve();
    }

    return fetch('/favorites', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        savedFavorites = data.map(f => f.place_id);

        // ✅ Update the savedFavorites in cache too
        const cached = localStorage.getItem('mosqueCache');
        if (cached) {
            const parsed = JSON.parse(cached);
            parsed.savedFavorites = savedFavorites;
            localStorage.setItem('mosqueCache', JSON.stringify(parsed));
        }
    })
    .catch(() => {
        savedFavorites = []; // fallback
    });
}

  
  
window.initMap = function() {  
    const defaultLocation = { lat: 43.6532, lng: -79.3832 };  
  
    map = new google.maps.Map(document.getElementById("map"), {  
        center: defaultLocation,  
        zoom: 13,  
    });  
  
document.getElementById("findMosquesBtn").addEventListener("click", () => {
    document.getElementById("loadingSpinner").classList.remove("d-none");

    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }

    navigator.geolocation.getCurrentPosition(position => {
        const userLocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
        };

        map.setCenter(userLocation);

        fetchSavedFavorites().then(() => {
            const cached = getCachedMosques(userLocation);
            if (cached) {
                listMosquesWithDirections(cached.mosques, userLocation, true, cached.savedFavorites).then(() => {
                    document.getElementById("loadingSpinner").classList.add("d-none");
                });
                return;
            }

            // No cache: Continue with API request
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
                keyword: 'mosque masjid'
            };

            service = new google.maps.places.PlacesService(map);
            service.nearbySearch(request, (results, status) => {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    const topMosques = results.slice(0, 3);
                    listMosquesWithDirections(topMosques, userLocation).then(() => {
                        document.getElementById("loadingSpinner").classList.add("d-none");
                    });
                } else {
                    document.getElementById("loadingSpinner").classList.add("d-none");
                }
            });
        });
    });
});

};        // No cache → fetch favorites then search from API  
      
  
 
function listMosquesWithDirections(mosques, userLocation, fromCache = false, cachedFavorites = []) {
    // ✅ Sync savedFavorites from cache if provided
    if (fromCache && Array.isArray(cachedFavorites)) {
        savedFavorites = cachedFavorites;
    }

    return new Promise((resolve) => {
        mosques.forEach(mosque => {
            mosque.place_id = mosque.place_id || mosque.placeId;
        });

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
            const placeId = mosque.place_id || mosque.placeId;
            if (!placeId) return;

            const detailsRequest = {
                placeId: placeId,
                fields: ['name', 'website', 'formatted_address', 'rating', 'formatted_phone_number']
            };

            placeService.getDetails(detailsRequest, (detailsResult, detailsStatus) => {
                const name = detailsResult?.name || mosque.name;
                const website = detailsResult?.website || '';
                const address = detailsResult?.formatted_address || '';
                const rating = detailsResult?.rating || null;
                const phone = detailsResult?.formatted_phone_number || '';
                const directionsRequest = {
                    origin: userLocation,
                    destination: { placeId: placeId },
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
                            placeId: placeId,
                            place_id: placeId,
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
                                <div class="fw-bold bg-warning text-dark px-1 py-1 rounded d-inline-block">${m.name}</div>${m.rating ? `  ⭐ ${m.rating.toFixed(1)}/5<br/>` : ""}                             📞  <a href="tel:${m.phone}">${m.phone}</a><br/>
                                Distance: ${m.distance}<br/>
                                ETA: ${m.durationText}<br/>
                                <a href="${directionsLink}" target="_blank"> Directions</a><br/>
                                ${m.website ? `<a href="${m.website}" target="_blank"> Website</a><br/>` : ""}
                                
                                ${isLoggedIn ? (
                                    savedFavorites.includes(m.place_id)
                                        ? `<span class="badge bg-success mt-1">✅ Saved as Favorite</span>`
                                        : (savedFavorites.length >= 3
                                            ? `<div class="text-muted mt-1">⚠️ Maximum 3 favorites reached</div>`
                                            : `<button class="btn btn-outline-danger btn-sm mt-2 save-fav-btn"
                                                    data-name="${m.name}"
                                                    data-place-id="${m.place_id}">
                                                    ❤️ Save to Favorites
                                               </button>`
                                          )
                                ) : ""}
                            `;

                            list.appendChild(li);
                        });

                        const cacheData = {
                            timestamp: Date.now(),
                            userLocation,
                            mosques: topThree,
                            savedFavorites: savedFavorites
                        };
                        localStorage.setItem('mosqueCache', JSON.stringify(cacheData));

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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name, place_id: placeId })
        })
        .then(res => res.json())
        .then(data => {
            // ✅ Show badge immediately
            e.target.outerHTML = `<span class="badge bg-success mt-2">✅ Saved as Favorite</span>`;

            // ✅ Re-fetch all favorites from server to sync
            fetch('/favorites')
                .then(res => res.json())
               .then(favs => {
    savedFavorites = favs.map(f => f.place_id);

    const cached = localStorage.getItem('mosqueCache');
    if (cached) {
        const parsed = JSON.parse(cached);
        parsed.savedFavorites = savedFavorites;
        localStorage.setItem('mosqueCache', JSON.stringify(parsed));

        if (parsed.mosques?.length && parsed.userLocation) {
            listMosquesWithDirections([...parsed.mosques], parsed.userLocation, true, savedFavorites);
        }
    }
});


            // ✅ Optional: show success alert
            const msgDiv = document.getElementById('favMessage');
            msgDiv.textContent = data.message;
            msgDiv.classList.remove('d-none');
            msgDiv.classList.add('show');

            setTimeout(() => {
                msgDiv.classList.add('d-none');
            }, 5000);
        })
        .catch(() => {
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
  
document.addEventListener('submit', function (e) {  
    if (e.target.matches('form') && e.target.querySelector('.remove-fav-btn')) {  
        // Wait a short time after form submit to let Laravel process the delete  
        setTimeout(() => {  
            // Re-fetch the updated favorites and update the cache  
            fetchSavedFavorites().then(() => {  
                const cached = localStorage.getItem('mosqueCache');  
                if (cached) {  
                    const parsed = JSON.parse(cached);  
                    parsed.savedFavorites = savedFavorites;   
                    localStorage.setItem('mosqueCache', JSON.stringify(parsed));  
                }  
            });  
        }, 1000);  
    }  
});    