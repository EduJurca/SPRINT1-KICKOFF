/**
 * JavaScript para la página de localizar vehículos
 * Gestiona mapas, listados y reclamación de vehículos
 */

// Safe translation helper to avoid inserting 'undefined' into templates
function t(key, fallback = '') {
    try {
        return (window.TRANSLATIONS && window.TRANSLATIONS[key]) || fallback;
    } catch (e) {
        return fallback;
    }
}

const VehicleLocator = {
    mobileMap: null,
    desktopMap: null,
    mobileMarkers: [],
    desktopMarkers: [],
    userLocation: null,
    vehicles: [],
    
    /**
     * Inicializar la página
     */
    async init() {
        // Obtener ubicación del usuario
        await this.getUserLocation();
        
        // Cargar vehículos
        await this.loadVehicles();
        
        // Inicializar mapas
        await this.initMaps();
        
        // Configurar UI
        this.setupUI();
    },
    
    /**
     * Obtener ubicación del usuario
     */
    async getUserLocation() {
        return new Promise((resolve) => {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        resolve(this.userLocation);
                    },
                    (error) => {
                        // Usar ubicación por defecto (Amposta)
                        this.userLocation = { lat: 40.7117, lng: 0.5783 };
                        resolve(this.userLocation);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                this.userLocation = { lat: 40.7117, lng: 0.5783 };
                resolve(this.userLocation);
            }
        });
    },
    
    /**
     * Cargar vehículos
     */
    async loadVehicles() {
        try {
            if (this.userLocation) {
                this.vehicles = await Vehicles.getAvailableVehicles(this.userLocation);
            } else {
                this.vehicles = await Vehicles.getAvailableVehicles();
            }
            
            // Calcular distancia para cada vehículo
            if (this.userLocation) {
                this.vehicles.forEach(vehicle => {
                    if (vehicle.location) {
                        vehicle.distance = this.calculateDistance(
                            this.userLocation,
                            vehicle.location
                        );
                    }
                });
                
                // Ordenar por distancia
                this.vehicles.sort((a, b) => (a.distance || Infinity) - (b.distance || Infinity));
            }
            
            // Actualizar listas
            this.updateVehicleLists();
            
        } catch (error) {
            this.vehicles = [];
        }
    },
    
    /**
     * Calcular distancia entre dos puntos
     */
    calculateDistance(coord1, coord2) {
        const R = 6371; // Radio de la Tierra en km
        const dLat = this.toRad(coord2.lat - coord1.lat);
        const dLng = this.toRad(coord2.lng - coord1.lng);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRad(coord1.lat)) * Math.cos(this.toRad(coord2.lat)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    },
    
    toRad(degrees) {
        return degrees * (Math.PI / 180);
    },
    
    /**
     * Inicializar mapas
     */
    async initMaps() {
        // Mapa móvil
        const mapMobileContainer = document.getElementById('map');
        if (mapMobileContainer && !mapMobileContainer._leaflet_id) {
            this.mobileMap = L.map('map').setView([this.userLocation.lat, this.userLocation.lng], 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                minZoom: 10,
                maxZoom: 18
            }).addTo(this.mobileMap);
            
            this.addUserMarker(this.mobileMap);
            this.addVehicleMarkers(this.mobileMap, this.mobileMarkers);
        }
        
        // Mapa desktop
        const mapDesktopContainer = document.getElementById('map-desktop');
        if (mapDesktopContainer && !mapDesktopContainer._leaflet_id) {
            this.desktopMap = L.map('map-desktop').setView([this.userLocation.lat, this.userLocation.lng], 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                minZoom: 10,
                maxZoom: 18
            }).addTo(this.desktopMap);
            
            this.addUserMarker(this.desktopMap);
            this.addVehicleMarkers(this.desktopMap, this.desktopMarkers);
        }
    },
    
    /**
     * Añadir marcador del usuario
     */
    addUserMarker(map) {
        if (!map || !this.userLocation) return;
        
        const userIcon = L.divIcon({
            className: 'user-marker',
            html: `
                <div style="
                    width: 24px;
                    height: 24px;
                    background-color: #1565C0;
                    border: 3px solid white;
                    border-radius: 50%;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                "></div>
            `,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
        
            L.marker([this.userLocation.lat, this.userLocation.lng], {
            icon: userIcon,
            zIndexOffset: 1000
        }).addTo(map).bindPopup(`<b>${t('vehicle.your_location', 'Your location')}</b>`);
    },

    addVehicleMarkers(map, markersArray) {
        if (!map) return;
        
        // Filtrar solo vehículos disponibles
        const availableVehicles = this.vehicles.filter(vehicle => vehicle.status === 'available');
        
        availableVehicles.forEach(vehicle => {
            if (!vehicle.location) return;
            
            const color = this.getBatteryColor(vehicle.battery);
            
            const vehicleIcon = L.divIcon({
                className: 'vehicle-marker',
                html: `
                    <div style="position: relative; width: 20px; height: 20px;">
                        <div style="
                            width: 30px;
                            height: 30px;
                            background-color: ${color};
                            border: 3px solid white;
                            border-radius: 50%;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 20px;
                        ">                       
                        <i class="fas fa-car text-sm"></i>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                popupAnchor: [0, -20]
            });
            
            const marker = L.marker([vehicle.location.lat, vehicle.location.lng], {
                icon: vehicleIcon
            }).addTo(map);
            
            marker.on('click', (e) => {
                e.target.closePopup();
                this.showVehicleDetails(vehicle.id);
            });
            
            markersArray.push({
                id: vehicle.id,
                marker: marker,
                vehicle: vehicle
            });
        });
    },
    

    getBatteryColor(battery) {
        if (battery >= 80) return '#10B981';
        if (battery >= 50) return '#F59E0B'; 
        if (battery >= 20) return '#F97316'; 
        return '#EF4444';
    },

    updateVehicleLists() {
        // Filtrar solo vehículos disponibles
        const availableVehicles = this.vehicles.filter(v => v.status === 'available');
        
        const normalVehicles = availableVehicles.filter(v => !v.is_accessible);
        const accessibleVehicles = availableVehicles.filter(v => v.is_accessible);
        
        this.renderVehicleList('normal-list', normalVehicles);
        this.renderVehicleList('special-list', accessibleVehicles);
        
        this.renderVehicleList('normal-list-2', normalVehicles);
        this.renderVehicleList('special-list-2', accessibleVehicles);
    },
    
    renderVehicleList(listId, vehicles) {
        const list = document.getElementById(listId);
        if (!list) return;
        
            if (vehicles.length === 0) {
            list.innerHTML = `
                <li class="bg-gray-100 p-4 rounded-lg shadow-sm text-center text-gray-500">
                    ${t('vehicle.no_vehicles_available', 'No vehicles available')}
                </li>
            `;
            return;
        }
        
        list.innerHTML = vehicles.map(vehicle => `
            <li class="bg-gray-100 p-4 rounded-lg shadow-sm flex items-center justify-between hover:bg-gray-200 transition-colors cursor-pointer"
                onclick="VehicleLocator.showVehicleDetails(${vehicle.id})">
                <div>
                    <h3 class="font-bold text-base">${vehicle.model || vehicle.license_plate}</h3>
                    <p class="text-gray-700 text-sm">${vehicle.battery}% ${t('vehicle.battery_unit', 'battery')}</p>
                    ${vehicle.distance ? `<p class="text-gray-700 text-xs">📍 ${vehicle.distance.toFixed(2)} km</p>` : ''}
                </div>
                <button 
                    onclick="event.stopPropagation(); VehicleLocator.handleClaimVehicle(${vehicle.id})"
                    class="bg-[#1565C0] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#1151a3] transition-colors duration-300">
                        ${t('vehicle.claim', 'Claim')}
                </button>
            </li>
        `).join('');
    },
    
    showVehicleDetails(vehicleId) {
        const vehicle = this.vehicles.find(v => v.id === vehicleId);
        if (!vehicle) return;

        if (this.mobileMap) {
            const m = this.mobileMarkers.find(x => x.id === vehicleId);
            if (m && m.marker) {
                this.mobileMap.setView(m.marker.getLatLng(), 16);
            }
        }
        if (this.desktopMap) {
            const d = this.desktopMarkers.find(x => x.id === vehicleId);
            if (d && d.marker) {
                this.desktopMap.setView(d.marker.getLatLng(), 16);
            }
        }

        const modal = document.getElementById('vehicle-details-modal');
        const overlay = document.getElementById('vehicle-details-overlay');
        const modalContent = modal?.querySelector('.vehicle-details-content');
        const nearbyList = modal?.querySelector('#nearby-vehicles-list');
        
        if (!modal || !modalContent || !nearbyList || !overlay) {
            console.error('Modal elements not found!');
            return;
        }

        const userDistance = this.userLocation && vehicle.location 
            ? this.calculateDistance(this.userLocation, vehicle.location)
            : null;

        modalContent.innerHTML = `
            <div class="p-6">
                <div class="flex gap-4 mb-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900">${vehicle.model}</h3>
                        <p class="text-sm text-gray-600 mt-1">${vehicle.license_plate || ''}</p>
                        ${userDistance ? `<p class="text-xs text-gray-500 mt-2 flex items-center"><i class="fas fa-map-marker-alt mr-1"></i> ${userDistance.toFixed(2)} km</p>` : ''}
                        <div class="flex items-center mt-2">
                            <i class="fas fa-battery-three-quarters mr-2 text-xl" style="color: ${this.getBatteryColor(vehicle.battery)}"></i>
                            <p class="text-base font-bold" style="color: ${this.getBatteryColor(vehicle.battery)}">${vehicle.battery}%</p>
                        </div>
                    </div>
                    ${vehicle.image_url ? `
                    <div class="flex-[2]">
                        <img src="${vehicle.image_url}" alt="${vehicle.model}" class="w-full max-w-[200px] h-auto object-cover rounded-lg">
                    </div>
                    ` : ''}
                </div>
                ${vehicle.description ? `<p class="mt-3 text-sm text-gray-700">${vehicle.description}</p>` : ''}
                <div class="mt-4">
                    <button id="vehicle-claim-btn" class="w-full bg-[#1565C0] hover:bg-[#0D47A1] text-white py-3 rounded-lg font-semibold transition-colors shadow-md">
                        <i class="fas fa-key mr-2"></i>${t('details.claim_this_vehicle', 'Claim this vehicle')}
                    </button>
                </div>
            </div>
        `;

        const others = this.vehicles
            .filter(v => v.id !== vehicleId && v.location)
            .map(v => ({ 
                ...v, 
                distanceFromSelected: this.calculateDistance(vehicle.location, v.location) 
            }))
            .sort((a, b) => (a.distanceFromSelected || Infinity) - (b.distanceFromSelected || Infinity));

        nearbyList.innerHTML = others.slice(0, 8).map(v => `
            <li class="flex justify-between items-center py-3 hover:bg-gray-50 transition-colors cursor-pointer" onclick="VehicleLocator.showVehicleDetails(${v.id})">
                <div class="flex items-center flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3" style="background-color: ${this.getBatteryColor(v.battery)}20;">
                        <i class="fas fa-car" style="color: ${this.getBatteryColor(v.battery)}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-sm">${v.model || v.license_plate}</div>
                        <div class="text-xs text-gray-500 flex items-center mt-1">
                            <i class="fas fa-location-arrow mr-1"></i>
                            ${v.distanceFromSelected ? v.distanceFromSelected.toFixed(2) + ' km' : ''}
                            <span class="mx-2">•</span>
                            <i class="fas fa-battery-three-quarters mr-1"></i>
                            ${v.battery}%
                        </div>
                    </div>
                </div>
                <button onclick="event.stopPropagation(); VehicleLocator.focusVehicle(${v.id})" class="text-sm text-[#1565C0] hover:text-[#0D47A1] font-medium px-3">
                    <i class="fas fa-eye"></i>
                </button>
            </li>
        `).join('');

        setTimeout(() => {
            const claimBtn = document.getElementById('vehicle-claim-btn');
            if (claimBtn) {
                claimBtn.onclick = () => this.handleClaimVehicle(vehicleId);
            }
        }, 0);

        overlay.style.display = 'block';
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        }, 10);

        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.remove('translate-y-full');
            modal.classList.add('translate-y-0');
        }, 10);

        overlay.onclick = () => this.closeVehicleDetails();
        
        document.body.style.overflow = 'hidden';
    },

    closeVehicleDetails() {
        const modal = document.getElementById('vehicle-details-modal');
        const overlay = document.getElementById('vehicle-details-overlay');
        if (!modal || !overlay) return;

        modal.classList.remove('translate-y-0');
        modal.classList.add('translate-y-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        
        setTimeout(() => { 
            modal.style.display = 'none'; 
            overlay.style.display = 'none';
        }, 300);
        
        document.body.style.overflow = '';
    },

    focusVehicle(vehicleId) {
        let item = this.mobileMarkers.find(m => m.id === vehicleId);
        if (item && item.marker && this.mobileMap) {
            this.mobileMap.setView(item.marker.getLatLng(), 16);
            item.marker.openPopup();
            return;
        }
        
        item = this.desktopMarkers.find(m => m.id === vehicleId);
        if (item && item.marker && this.desktopMap) {
            this.desktopMap.setView(item.marker.getLatLng(), 16);
            item.marker.openPopup();
        }
    },
    
    handleClaimVehicle(vehicleId) {
        const vehicle = this.vehicles.find(v => v.id === vehicleId);
        
        if (!vehicle) {
            return;
        }
        
        this.closeVehicleDetails();
        
        if (typeof window.showClaimModal === 'function') {
            window.showClaimModal(vehicle);
        } else {
            Vehicles.claimVehicle(vehicleId);
        }
    },

    setupUI() {
        const toggleButtons = document.querySelectorAll('#toggle-vehicles, #toggle-vehicles-2');
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const parent = button.closest('.mobile-view') || button.closest('.desktop-view');
                const normalList = parent.querySelector('[id^="normal-list"]');
                const specialList = parent.querySelector('[id^="special-list"]');
                
                if (normalList && specialList) {
                    normalList.classList.toggle('hidden');
                    specialList.classList.toggle('hidden');
                }
            });
        });
        


        // Botón cerrar modal
        const closeVehicleBtn = document.getElementById('close-vehicle-details');
        if (closeVehicleBtn) {
            closeVehicleBtn.addEventListener('click', () => {
                this.closeVehicleDetails();
            });
        }
    }
};

/**
 * Inicializar cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', async () => {
    // Esperar a que Leaflet esté cargado
    if (typeof L === 'undefined') {
        return;
    }
    
    await VehicleLocator.init();
});

// Exportar para uso global
window.VehicleLocator = VehicleLocator;
