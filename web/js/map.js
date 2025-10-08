/**
 * Map Module
 * Handles Leaflet.js map integration and vehicle marker display
 */

class VehicleMap {
  constructor(mapElementId = 'map') {
    this.mapElementId = mapElementId;
    this.map = null;
    this.markers = [];
    this.userMarker = null;
    this.defaultCenter = [40.4168, -3.7038]; // Madrid, Spain
    this.defaultZoom = 13;
    this.vehicles = [];
    this.activeFilter = 'all';
  }

  /**
   * Initialize the map
   */
  async init() {
    const mapElement = document.getElementById(this.mapElementId);
    if (!mapElement) {
      console.error(`Map element with id '${this.mapElementId}' not found`);
      return;
    }

    // Create map instance
    this.map = L.map(this.mapElementId).setView(this.defaultCenter, this.defaultZoom);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(this.map);

    // Try to get user location
    this.getUserLocation();

    // Setup filter buttons
    this.setupFilters();

    // Load vehicles
    await this.loadVehicles();
  }

  /**
   * Get user's current location
   */
  getUserLocation() {
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          this.map.setView([latitude, longitude], this.defaultZoom);
          
          // Add user location marker
          this.userMarker = L.marker([latitude, longitude], {
            icon: L.divIcon({
              className: 'user-location-marker',
              html: '<div class="w-4 h-4 bg-blue-600 rounded-full border-2 border-white shadow-lg"></div>',
              iconSize: [16, 16]
            })
          }).addTo(this.map);
          
          this.userMarker.bindPopup('Your Location').openPopup();
        },
        (error) => {
          console.warn('Geolocation error:', error.message);
        }
      );
    }
  }

  /**
   * Load vehicles from API
   */
  async loadVehicles() {
    try {
      // In production, this would fetch from API
      // For now, use demo data
      this.vehicles = this.getDemoVehicles();
      this.displayVehicles();
    } catch (error) {
      console.error('Error loading vehicles:', error);
    }
  }

  /**
   * Get demo vehicles data
   */
  getDemoVehicles() {
    return [
      {
        id: 1,
        model: 'Tesla Model 3',
        type: 'electric',
        plate: 'ABC-1234',
        status: 'available',
        battery: 85,
        range: 340,
        location: { lat: 40.4168, lng: -3.7038 },
        pricePerHour: 12
      },
      {
        id: 2,
        model: 'Nissan Leaf',
        type: 'electric',
        plate: 'DEF-5678',
        status: 'available',
        battery: 92,
        range: 270,
        location: { lat: 40.4200, lng: -3.7050 },
        pricePerHour: 10
      },
      {
        id: 3,
        model: 'Toyota Prius',
        type: 'hybrid',
        plate: 'GHI-9012',
        status: 'in_use',
        battery: 65,
        range: 450,
        location: { lat: 40.4150, lng: -3.7100 },
        pricePerHour: 9
      },
      {
        id: 4,
        model: 'BMW i3',
        type: 'electric',
        plate: 'JKL-3456',
        status: 'available',
        battery: 78,
        range: 260,
        location: { lat: 40.4180, lng: -3.7020 },
        pricePerHour: 11
      },
      {
        id: 5,
        model: 'Renault Zoe',
        type: 'electric',
        plate: 'MNO-7890',
        status: 'maintenance',
        battery: 45,
        range: 180,
        location: { lat: 40.4140, lng: -3.7080 },
        pricePerHour: 8
      },
      {
        id: 6,
        model: 'Hyundai Kona',
        type: 'electric',
        plate: 'PQR-2345',
        status: 'available',
        battery: 95,
        range: 415,
        location: { lat: 40.4190, lng: -3.7010 },
        pricePerHour: 13
      }
    ];
  }

  /**
   * Display vehicles on map
   */
  displayVehicles() {
    // Clear existing markers
    this.clearMarkers();

    // Filter vehicles
    const filteredVehicles = this.filterVehicles();

    // Add markers for each vehicle
    filteredVehicles.forEach(vehicle => {
      const marker = this.createVehicleMarker(vehicle);
      this.markers.push(marker);
    });
  }

  /**
   * Filter vehicles based on active filter
   */
  filterVehicles() {
    if (this.activeFilter === 'all') {
      return this.vehicles;
    } else if (this.activeFilter === 'available') {
      return this.vehicles.filter(v => v.status === 'available');
    } else if (this.activeFilter === 'electric') {
      return this.vehicles.filter(v => v.type === 'electric');
    } else if (this.activeFilter === 'hybrid') {
      return this.vehicles.filter(v => v.type === 'hybrid');
    }
    return this.vehicles;
  }

  /**
   * Create marker for a vehicle
   */
  createVehicleMarker(vehicle) {
    const icon = this.getVehicleIcon(vehicle);
    const marker = L.marker([vehicle.location.lat, vehicle.location.lng], { icon })
      .addTo(this.map);

    // Create popup content
    const popupContent = this.createPopupContent(vehicle);
    marker.bindPopup(popupContent);

    // Store vehicle data with marker
    marker.vehicleData = vehicle;

    return marker;
  }

  /**
   * Get icon for vehicle based on status
   */
  getVehicleIcon(vehicle) {
    let color = '#10b981'; // green for available
    
    if (vehicle.status === 'in_use') {
      color = '#ef4444'; // red
    } else if (vehicle.status === 'maintenance') {
      color = '#f59e0b'; // orange
    }

    return L.divIcon({
      className: 'vehicle-marker',
      html: `
        <div class="relative">
          <svg class="w-8 h-8" fill="${color}" viewBox="0 0 24 24">
            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
          </svg>
        </div>
      `,
      iconSize: [32, 32],
      iconAnchor: [16, 32],
      popupAnchor: [0, -32]
    });
  }

  /**
   * Create popup content for vehicle
   */
  createPopupContent(vehicle) {
    const statusText = {
      available: 'Available',
      in_use: 'In Use',
      maintenance: 'Maintenance'
    };

    const statusColor = {
      available: 'text-green-600',
      in_use: 'text-red-600',
      maintenance: 'text-orange-600'
    };

    return `
      <div class="p-2 min-w-[200px]">
        <h3 class="font-bold text-lg mb-2">${vehicle.model}</h3>
        <div class="space-y-1 text-sm">
          <p><span class="font-semibold">Plate:</span> ${vehicle.plate}</p>
          <p><span class="font-semibold">Status:</span> <span class="${statusColor[vehicle.status]}">${statusText[vehicle.status]}</span></p>
          <p><span class="font-semibold">Battery:</span> ${vehicle.battery}%</p>
          <p><span class="font-semibold">Range:</span> ${vehicle.range} km</p>
          <p><span class="font-semibold">Price:</span> €${vehicle.pricePerHour}/hour</p>
        </div>
        ${vehicle.status === 'available' ? `
          <button 
            onclick="bookVehicle(${vehicle.id})" 
            class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Book Now
          </button>
        ` : ''}
      </div>
    `;
  }

  /**
   * Clear all vehicle markers
   */
  clearMarkers() {
    this.markers.forEach(marker => {
      this.map.removeLayer(marker);
    });
    this.markers = [];
  }

  /**
   * Setup filter buttons
   */
  setupFilters() {
    document.querySelectorAll('[data-filter]').forEach(button => {
      button.addEventListener('click', () => {
        const filter = button.getAttribute('data-filter');
        this.setFilter(filter);
        
        // Update active button state
        document.querySelectorAll('[data-filter]').forEach(btn => {
          btn.classList.remove('bg-blue-600', 'text-white');
          btn.classList.add('bg-gray-200', 'text-gray-700');
        });
        button.classList.remove('bg-gray-200', 'text-gray-700');
        button.classList.add('bg-blue-600', 'text-white');
      });
    });
  }

  /**
   * Set active filter
   */
  setFilter(filter) {
    this.activeFilter = filter;
    this.displayVehicles();
  }

  /**
   * Center map on vehicle
   */
  centerOnVehicle(vehicleId) {
    const vehicle = this.vehicles.find(v => v.id === vehicleId);
    if (vehicle) {
      this.map.setView([vehicle.location.lat, vehicle.location.lng], 16);
      
      // Find and open marker popup
      const marker = this.markers.find(m => m.vehicleData.id === vehicleId);
      if (marker) {
        marker.openPopup();
      }
    }
  }

  /**
   * Refresh vehicle data
   */
  async refresh() {
    await this.loadVehicles();
  }
}

// Global function for booking (called from popup)
/* eslint-disable no-unused-vars, no-undef */
function bookVehicle(vehicleId) {
  if (typeof auth !== 'undefined' && !auth.isAuthenticated()) {
    window.location.href = './login.html';
    return;
  }
  
  // In production, this would make an API call
  alert(`Booking vehicle ${vehicleId}. This will be connected to the API.`);
}
/* eslint-enable no-unused-vars, no-undef */

// Export for use in other modules
/* eslint-disable no-undef */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = VehicleMap;
}
/* eslint-enable no-undef */
