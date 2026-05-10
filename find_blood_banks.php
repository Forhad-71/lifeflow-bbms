<?php
// find_blood_banks.php - LifeFlow Blood Bank Finder with Google Maps
session_start();
require "config.php";

$pageTitle = "Find Blood Banks - LifeFlow";
include 'includes/header.php';
?>

<style>
.finder {
    padding: 40px 0;
}

.finder__content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    margin-top: 30px;
}

@media (max-width: 900px) {
    .finder__content {
        grid-template-columns: 1fr;
    }
}

.finder__filters {
    background: var(--card-bg);
    border-radius: var(--radius-xl);
    padding: 25px;
    border: 1px solid rgba(255,255,255,0.1);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.filter-group {
    margin-bottom: 25px;
}

.filter-group label {
    display: block;
    color: var(--text-secondary);
    margin-bottom: 12px;
    font-weight: 500;
}

.blood-type-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.blood-btn {
    padding: 10px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.05);
    color: var(--text-secondary);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.blood-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.blood-btn.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.range-slider {
    display: flex;
    align-items: center;
    gap: 15px;
}

.range-slider input[type="range"] {
    flex: 1;
    height: 6px;
    -webkit-appearance: none;
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
    outline: none;
}

.range-slider input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: var(--primary);
    border-radius: 50%;
    cursor: pointer;
}

.range-value {
    background: rgba(255,255,255,0.1);
    padding: 5px 12px;
    border-radius: var(--radius-md);
    font-weight: 600;
    min-width: 60px;
    text-align: center;
}

.finder__map {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.map-container {
    width: 100%;
    height: 400px;
    background: var(--card-bg);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
}

.map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
}

.map-placeholder i {
    font-size: 4rem;
    margin-bottom: 15px;
    color: var(--primary);
}

.map-note {
    font-size: 0.8rem;
    margin-top: 10px;
    opacity: 0.6;
}

.blood-banks-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.bank-card {
    background: var(--card-bg);
    border-radius: var(--radius-xl);
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
}

.bank-card:hover {
    border-color: var(--primary);
    transform: translateY(-5px);
}

.bank-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.bank-card__header h4 {
    margin: 0;
    color: white;
    font-size: 1.1rem;
}

.distance {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.bank-card__info {
    margin-bottom: 15px;
}

.bank-card__info p {
    margin: 8px 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.bank-card__info i {
    width: 20px;
    color: var(--primary);
}

.bank-card__stock {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
}

.stock-item {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.stock-item.available {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.stock-item.low {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.stock-item.unavailable {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.bank-card__rating {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.stars {
    color: #fbbf24;
}

.stars i {
    font-size: 0.85rem;
}

.bank-card__rating span {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--text-muted);
}

.loading-spinner i {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 15px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>

<div class="page-wrapper">
    <div class="page-content">
        <!-- Header -->
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-map-marked-alt" style="color: var(--primary);"></i> Find Blood Banks</h1>
            <p class="page-subtitle">Locate the nearest blood banks using Google Maps</p>
        </div>
        
        <div class="finder__content">
            <!-- Filters Sidebar -->
            <div class="finder__filters" id="filtersCard">
                <div class="filter-group">
                    <label><i class="fas fa-tint"></i> Blood Type Needed</label>
                    <div class="blood-type-selector">
                        <button class="blood-btn active" data-type="all">All</button>
                        <button class="blood-btn" data-type="A+">A+</button>
                        <button class="blood-btn" data-type="A-">A-</button>
                        <button class="blood-btn" data-type="B+">B+</button>
                        <button class="blood-btn" data-type="B-">B-</button>
                        <button class="blood-btn" data-type="AB+">AB+</button>
                        <button class="blood-btn" data-type="AB-">AB-</button>
                        <button class="blood-btn" data-type="O+">O+</button>
                        <button class="blood-btn" data-type="O-">O-</button>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-ruler"></i> Search Radius</label>
                    <div class="range-slider">
                        <input type="range" min="1" max="50" value="10" id="radiusSlider">
                        <span class="range-value"><span id="radiusValue">10</span> km</span>
                    </div>
                </div>
                
                <button class="btn btn--primary btn--full" id="findNearbyBtn" onclick="findNearbyBloodBanks()">
                    <i class="fas fa-location-crosshairs"></i>
                    Find Nearby Blood Banks
                </button>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <button class="btn btn--outline btn--full" onclick="useCurrentLocation()">
                        <i class="fas fa-crosshairs"></i>
                        Use My Location
                    </button>
                </div>
            </div>
            
            <!-- Map & Results -->
            <div class="finder__map">
                <div class="map-container" id="map">
                    <div class="map-placeholder" id="mapPlaceholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>Click "Find Nearby Blood Banks" to load map</p>
                        <span class="map-note">Powered by Google Maps</span>
                    </div>
                </div>
                
                <h3 style="margin: 10px 0;"><i class="fas fa-hospital" style="color: var(--primary);"></i> Nearby Blood Banks</h3>
                
                <div class="blood-banks-list" id="bloodBanksList">
                    <!-- Blood banks will be loaded here -->
                    <div class="bank-card">
                        <div class="bank-card__header">
                            <h4>Chattogram Medical College Blood Bank</h4>
                            <span class="distance">1.2 km</span>
                        </div>
                        <div class="bank-card__info">
                            <p><i class="fas fa-map-pin"></i> K.B. Fazlul Kader Road, Chattogram</p>
                            <p><i class="fas fa-clock"></i> Open 24/7</p>
                            <p><i class="fas fa-phone"></i> +880 31 630 335</p>
                        </div>
                        <div class="bank-card__stock">
                            <span class="stock-item available">A+ ✓</span>
                            <span class="stock-item available">B+ ✓</span>
                            <span class="stock-item available">O+ ✓</span>
                            <span class="stock-item low">AB- ⚠</span>
                        </div>
                        <div class="bank-card__rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span>4.5 (128 reviews)</span>
                        </div>
                        <button class="btn btn--outline btn--small" onclick="getDirections(22.3569, 91.8317)">
                            <i class="fas fa-directions"></i> Get Directions
                        </button>
                    </div>
                    
                    <div class="bank-card">
                        <div class="bank-card__header">
                            <h4>Red Crescent Blood Bank</h4>
                            <span class="distance">2.8 km</span>
                        </div>
                        <div class="bank-card__info">
                            <p><i class="fas fa-map-pin"></i> Agrabad, Chattogram</p>
                            <p><i class="fas fa-clock"></i> 8AM - 10PM</p>
                            <p><i class="fas fa-phone"></i> +880 31 713 334</p>
                        </div>
                        <div class="bank-card__stock">
                            <span class="stock-item available">A+ ✓</span>
                            <span class="stock-item available">O+ ✓</span>
                            <span class="stock-item available">B- ✓</span>
                            <span class="stock-item unavailable">O- ✗</span>
                        </div>
                        <div class="bank-card__rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span>5.0 (256 reviews)</span>
                        </div>
                        <button class="btn btn--outline btn--small" onclick="getDirections(22.3285, 91.8137)">
                            <i class="fas fa-directions"></i> Get Directions
                        </button>
                    </div>
                    
                    <div class="bank-card">
                        <div class="bank-card__header">
                            <h4>Quantum Blood Bank</h4>
                            <span class="distance">3.5 km</span>
                        </div>
                        <div class="bank-card__info">
                            <p><i class="fas fa-map-pin"></i> Nasirabad, Chattogram</p>
                            <p><i class="fas fa-clock"></i> 9AM - 9PM</p>
                            <p><i class="fas fa-phone"></i> +880 31 655 789</p>
                        </div>
                        <div class="bank-card__stock">
                            <span class="stock-item available">A+ ✓</span>
                            <span class="stock-item available">B+ ✓</span>
                            <span class="stock-item low">O- ⚠</span>
                            <span class="stock-item available">AB+ ✓</span>
                        </div>
                        <div class="bank-card__rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span>4.0 (89 reviews)</span>
                        </div>
                        <button class="btn btn--outline btn--small" onclick="getDirections(22.3725, 91.8195)">
                            <i class="fas fa-directions"></i> Get Directions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMapReady" async defer></script>

<script>
let map;
let markers = [];
let userLocation = { lat: 22.3569, lng: 91.8317 }; // Default: Chattogram

// Blood type filter
document.querySelectorAll('.blood-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.blood-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterBloodBanks(this.dataset.type);
    });
});

// Radius slider
document.getElementById('radiusSlider').addEventListener('input', function() {
    document.getElementById('radiusValue').textContent = this.value;
});

// Initialize map when API loads
function initMapReady() {
    console.log('Google Maps API ready');
}

// Find nearby blood banks
function findNearbyBloodBanks() {
    const mapContainer = document.getElementById('map');
    const placeholder = document.getElementById('mapPlaceholder');
    
    // Show loading
    placeholder.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner"></i>
            <p>Finding blood banks near you...</p>
        </div>
    `;
    
    // Get user location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                initMap();
            },
            (error) => {
                console.log('Geolocation error:', error);
                Toast.show('Using default location (Chattogram)', 'warning');
                initMap();
            }
        );
    } else {
        Toast.show('Geolocation not supported', 'warning');
        initMap();
    }
}

// Initialize map
function initMap() {
    const mapContainer = document.getElementById('map');
    
    // Check if Google Maps is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        // Show embedded map instead
        mapContainer.innerHTML = `
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d59057.69784862182!2d91.78!3d22.35!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sblood%20bank%20near%20chattogram!5e0!3m2!1sen!2sbd!4v1699999999999!5m2!1sen!2sbd"
                width="100%" 
                height="100%" 
                style="border:0; border-radius: var(--radius-xl);" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        `;
        Toast.show('Map loaded!', 'success');
        return;
    }
    
    // Create map with Google Maps API
    map = new google.maps.Map(mapContainer, {
        center: userLocation,
        zoom: 13,
        styles: [
            { elementType: "geometry", stylers: [{ color: "#1a1a2e" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#1a1a2e" }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#8a8a8a" }] },
            { featureType: "road", elementType: "geometry", stylers: [{ color: "#2a2a4a" }] },
            { featureType: "water", elementType: "geometry", stylers: [{ color: "#0f0f1a" }] }
        ]
    });
    
    // Add user marker
    new google.maps.Marker({
        position: userLocation,
        map: map,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: "#4285F4",
            fillOpacity: 1,
            strokeWeight: 2,
            strokeColor: "#FFFFFF"
        },
        title: "Your Location"
    });
    
    // Add blood bank markers
    addBloodBankMarkers();
    
    Toast.show('Map loaded successfully!', 'success');
}

// Add blood bank markers
function addBloodBankMarkers() {
    const bloodBanks = [
        { name: "Chattogram Medical College Blood Bank", lat: 22.3569, lng: 91.8317 },
        { name: "Red Crescent Blood Bank", lat: 22.3285, lng: 91.8137 },
        { name: "Quantum Blood Bank", lat: 22.3725, lng: 91.8195 }
    ];
    
    bloodBanks.forEach(bank => {
        const marker = new google.maps.Marker({
            position: { lat: bank.lat, lng: bank.lng },
            map: map,
            icon: {
                url: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23c41e3a'%3E%3Cpath d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z'/%3E%3C/svg%3E",
                scaledSize: new google.maps.Size(40, 40)
            },
            title: bank.name
        });
        markers.push(marker);
    });
}

// Use current location
function useCurrentLocation() {
    if (navigator.geolocation) {
        Toast.show('Getting your location...', 'info');
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                Toast.show('Location updated!', 'success');
                if (map) {
                    map.setCenter(userLocation);
                }
            },
            (error) => {
                Toast.show('Could not get your location', 'error');
            }
        );
    } else {
        Toast.show('Geolocation not supported by your browser', 'error');
    }
}

// Get directions
function getDirections(lat, lng) {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    window.open(url, '_blank');
}

// Filter blood banks by type
function filterBloodBanks(type) {
    console.log('Filtering by:', type);
    // In a real app, this would filter the blood bank cards
    Toast.show(`Filtering for ${type === 'all' ? 'all blood types' : type}`, 'info');
}

// Animations
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#filtersCard', { x: -30, opacity: 0, duration: 0.5, delay: 0.2 });
    gsap.from('#map', { y: 30, opacity: 0, duration: 0.5, delay: 0.3 });
    gsap.from('.bank-card', { y: 20, opacity: 0, duration: 0.4, stagger: 0.1, delay: 0.4 });
}
</script>
