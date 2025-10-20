/**
 * Vehicle Management Module for VoltiaCar Application
 * Handles vehicle operations, control, and status
 */

/* global Utils */

const Vehicles = {
    currentVehicle: null,
    
    /**
     * Get API base path based on current location
     */
    getApiBasePath() {
        // Determine the correct path based on current location
        if (window.location.pathname.includes('/pages/')) {
            return '../../php/api/vehicles.php';
        }
        return './php/api/vehicles.php';
    },
    
    /**
     * Get available vehicles
     */
    async getAvailableVehicles(userLocation = null) {
        try {
            const basePath = this.getApiBasePath();
            let url = `${basePath}?action=available`;
            
            // If user location provided, use nearby endpoint
            if (userLocation && userLocation.lat && userLocation.lng) {
                url = `${basePath}?action=nearby&lat=${userLocation.lat}&lng=${userLocation.lng}&radius=10`;
            }
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    console.log('🚗 Vehicles API response:', data);
                    if (data.success && data.vehicles) {
                        return data.vehicles;
                    }
                }
            }
            
            // Return mock data for development
            console.log('⚠️ Using mock vehicles data');
            return this.getMockVehicles();
        } catch (error) {
            console.error('Error fetching vehicles:', error);
            return this.getMockVehicles();
        }
    },
    
    /**
     * Get nearby vehicles with filters
     */
    async getNearbyVehicles(lat, lng, filters = {}) {
        try {
            const basePath = this.getApiBasePath();
            let url = `${basePath}?action=nearby&lat=${lat}&lng=${lng}`;
            
            if (filters.radius) {
                url += `&radius=${filters.radius}`;
            }
            if (filters.type) {
                url += `&type=${filters.type}`;
            }
            if (filters.min_battery) {
                url += `&min_battery=${filters.min_battery}`;
            }
            if (filters.accessible) {
                url += `&accessible=true`;
            }
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    console.log('🔍 Nearby vehicles API response:', data);
                    if (data.success && data.vehicles) {
                        return data.vehicles;
                    }
                }
            }
            
            return [];
        } catch (error) {
            console.error('Error fetching nearby vehicles:', error);
            return [];
        }
    },
    
    /**
     * Get mock vehicles for development
     */
    getMockVehicles() {
        return [
            {
                id: 1,
                license_plate: 'AB 123 CD',
                model: 'Tesla Model 3',
                battery: 85,
                location: { lat: 41.3851, lng: 2.1734 },
                status: 'available',
                features: ['Mobilitat reduïda', 'Discapacitat auditiva']
            },
            {
                id: 2,
                license_plate: 'EF 456 GH',
                model: 'Nissan Leaf',
                battery: 70,
                location: { lat: 41.3879, lng: 2.1699 },
                status: 'available',
                features: []
            },
            {
                id: 3,
                license_plate: 'IJ 789 KL',
                model: 'BMW i3',
                battery: 60,
                location: { lat: 41.3901, lng: 2.1740 },
                status: 'available',
                features: ['Mobilitat reduïda']
            },
            {
                id: 4,
                license_plate: 'MN 012 OP',
                model: 'Renault Zoe',
                battery: 90,
                location: { lat: 41.3825, lng: 2.1765 },
                status: 'available',
                features: []
            },
            {
                id: 5,
                license_plate: 'QR 345 ST',
                model: 'Volkswagen ID.3',
                battery: 75,
                location: { lat: 41.3890, lng: 2.1710 },
                status: 'available',
                features: ['Discapacitat auditiva']
            }
        ];
    },
    
    /**
     * Get vehicle details
     */
    async getVehicleDetails(vehicleId) {
        try {
            const response = await fetch(`/php/api/vehicles.php?action=details&id=${vehicleId}`, {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.vehicle;
            }
            
            // Return mock data
            const vehicles = this.getMockVehicles();
            return vehicles.find(v => v.id === parseInt(vehicleId));
        } catch (error) {
            console.error('Error fetching vehicle details:', error);
            return null;
        }
    },
    
    /**
     * Claim a vehicle
     */
    async claimVehicle(vehicleId) {
        try {
            Utils.showLoading();
            
            const response = await fetch('/php/api/vehicles.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'claim',
                    vehicle_id: vehicleId
                })
            });
            
            const data = await response.json();
            Utils.hideLoading();
            
            if (data.success) {
                this.currentVehicle = data.vehicle;
                Utils.setLocalStorage('currentVehicle', data.vehicle, 60);
                Utils.showToast('Vehicle reclamat amb èxit', 'success');
                
                setTimeout(() => {
                    window.location.href = './administrar-vehicle.html';
                }, 1000);
                
                return { success: true };
            } else {
                Utils.showToast(data.message || 'Error en reclamar el vehicle', 'error');
                return { success: false };
            }
        } catch (error) {
            Utils.hideLoading();
            console.error('Error claiming vehicle:', error);
            Utils.showToast('Error de connexió amb el servidor', 'error');
            return { success: false };
        }
    },
    
    /**
     * Release current vehicle
     */
    async releaseVehicle() {
        try {
            Utils.showLoading();
            
            const response = await fetch('/php/api/vehicles.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'release'
                })
            });
            
            const data = await response.json();
            Utils.hideLoading();
            
            if (data.success) {
                this.currentVehicle = null;
                localStorage.removeItem('currentVehicle');
                Utils.showToast('Vehicle alliberat amb èxit', 'success');
                return { success: true };
            } else {
                Utils.showToast(data.message || 'Error en alliberar el vehicle', 'error');
                return { success: false };
            }
        } catch (error) {
            Utils.hideLoading();
            console.error('Error releasing vehicle:', error);
            Utils.showToast('Error de connexió amb el servidor', 'error');
            return { success: false };
        }
    },
    
    /**
     * Get current vehicle
     */
    getCurrentVehicle() {
        if (!this.currentVehicle) {
            this.currentVehicle = Utils.getLocalStorage('currentVehicle');
        }
        return this.currentVehicle;
    },
    
    /**
     * Control vehicle - activate horn
     */
    async activateHorn() {
        try {
            const response = await fetch('/php/api/vehicle-control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'horn'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Utils.showToast('Clàxon activat', 'success');
                return { success: true };
            } else {
                Utils.showToast('Error en activar el clàxon', 'error');
                return { success: false };
            }
        } catch (error) {
            console.error('Error activating horn:', error);
            Utils.showToast('Error de connexió', 'error');
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - activate lights
     */
    async activateLights() {
        try {
            const response = await fetch('/php/api/vehicle-control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'lights'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Utils.showToast('Llums activats', 'success');
                return { success: true };
            } else {
                Utils.showToast('Error en activar els llums', 'error');
                return { success: false };
            }
        } catch (error) {
            console.error('Error activating lights:', error);
            Utils.showToast('Error de connexió', 'error');
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - start engine
     */
    async startEngine() {
        try {
            Utils.showLoading();
            
            const response = await fetch('/php/api/vehicle-control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'start'
                })
            });
            
            const data = await response.json();
            Utils.hideLoading();
            
            if (data.success) {
                Utils.showToast('Motor engegat', 'success');
                return { success: true };
            } else {
                Utils.showToast('Error en engegar el motor', 'error');
                return { success: false };
            }
        } catch (error) {
            Utils.hideLoading();
            console.error('Error starting engine:', error);
            Utils.showToast('Error de connexió', 'error');
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - stop engine
     */
    async stopEngine() {
        try {
            Utils.showLoading();
            
            const response = await fetch('/php/api/vehicle-control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'stop'
                })
            });
            
            const data = await response.json();
            Utils.hideLoading();
            
            if (data.success) {
                Utils.showToast('Motor apagat', 'success');
                return { success: true };
            } else {
                Utils.showToast('Error en apagar el motor', 'error');
                return { success: false };
            }
        } catch (error) {
            Utils.hideLoading();
            console.error('Error stopping engine:', error);
            Utils.showToast('Error de connexió', 'error');
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - lock/unlock doors
     */
    async toggleDoors(lock = true) {
        try {
            const response = await fetch('/php/api/vehicle-control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    action: lock ? 'lock' : 'unlock'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Utils.showToast(lock ? 'Portes bloquejades' : 'Portes desbloquejades', 'success');
                return { success: true };
            } else {
                Utils.showToast('Error en controlar les portes', 'error');
                return { success: false };
            }
        } catch (error) {
            console.error('Error toggling doors:', error);
            Utils.showToast('Error de connexió', 'error');
            return { success: false };
        }
    },
    
    /**
     * Get vehicle battery status
     */
    getBatteryStatus(battery) {
        if (battery >= 80) {
            return { level: 'high', color: 'text-green-600', icon: '🔋' };
        } else if (battery >= 50) {
            return { level: 'medium', color: 'text-yellow-600', icon: '🔋' };
        } else if (battery >= 20) {
            return { level: 'low', color: 'text-orange-600', icon: '🪫' };
        } else {
            return { level: 'critical', color: 'text-red-600', icon: '🪫' };
        }
    },
    
    /**
     * Calculate estimated range based on battery
     */
    calculateRange(battery) {
        const maxRange = 300; // km
        return Math.round((battery / 100) * maxRange);
    },
    
    /**
     * Filter vehicles by criteria
     */
    filterVehicles(vehicles, filters) {
        return vehicles.filter(vehicle => {
            // Filter by minimum battery
            if (filters.minBattery && vehicle.battery < filters.minBattery) {
                return false;
            }
            
            // Filter by features
            if (filters.features && filters.features.length > 0) {
                const hasAllFeatures = filters.features.every(feature => 
                    vehicle.features.includes(feature)
                );
                if (!hasAllFeatures) {
                    return false;
                }
            }
            
            // Filter by distance (if user location provided)
            if (filters.maxDistance && filters.userLocation) {
                const distance = this.calculateDistance(
                    filters.userLocation,
                    vehicle.location
                );
                if (distance > filters.maxDistance) {
                    return false;
                }
            }
            
            return true;
        });
    },
    
    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    calculateDistance(coord1, coord2) {
        const R = 6371; // Earth radius in km
        const dLat = this.toRad(coord2.lat - coord1.lat);
        const dLng = this.toRad(coord2.lng - coord1.lng);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRad(coord1.lat)) * Math.cos(this.toRad(coord2.lat)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = R * c;
        
        return distance;
    },
    
    /**
     * Convert degrees to radians
     */
    toRad(degrees) {
        return degrees * (Math.PI / 180);
    },
    
    /**
     * Sort vehicles by criteria
     */
    sortVehicles(vehicles, sortBy = 'battery') {
        const sorted = [...vehicles];
        
        switch (sortBy) {
            case 'battery':
                return sorted.sort((a, b) => b.battery - a.battery);
            case 'distance':
                // Requires user location
                return sorted;
            case 'model':
                return sorted.sort((a, b) => a.model.localeCompare(b.model));
            default:
                return sorted;
        }
    }
};

/**
 * Setup vehicle control buttons
 */
function setupVehicleControls() {
    // Horn button
    const hornButton = document.getElementById('hornButton');
    if (hornButton) {
        hornButton.addEventListener('click', async () => {
            await Vehicles.activateHorn();
        });
    }
    
    // Lights button
    const lightsButton = document.getElementById('lightsButton');
    if (lightsButton) {
        lightsButton.addEventListener('click', async () => {
            await Vehicles.activateLights();
        });
    }
    
    // Start engine button
    const startButton = document.getElementById('startButton');
    if (startButton) {
        startButton.addEventListener('click', async () => {
            await Vehicles.startEngine();
        });
    }
    
    // Stop engine button
    const stopButton = document.getElementById('stopButton');
    if (stopButton) {
        stopButton.addEventListener('click', async () => {
            await Vehicles.stopEngine();
        });
    }
    
    // Lock doors button
    const lockButton = document.getElementById('lockButton');
    if (lockButton) {
        lockButton.addEventListener('click', async () => {
            await Vehicles.toggleDoors(true);
        });
    }
    
    // Unlock doors button
    const unlockButton = document.getElementById('unlockButton');
    if (unlockButton) {
        unlockButton.addEventListener('click', async () => {
            await Vehicles.toggleDoors(false);
        });
    }
}

/**
 * Initialize vehicle module
 */
document.addEventListener('DOMContentLoaded', () => {
    setupVehicleControls();
});

// Export for use in other scripts
window.Vehicles = Vehicles;
