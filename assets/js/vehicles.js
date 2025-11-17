/**
 * Vehicle Management Module for VoltiaCar Application
 * Handles vehicle operations, control, and status
 */

const Vehicles = {
    currentVehicle: null,
    
    /**
     * Get API base path based on current location
     */
    getApiBasePath() {
        // Use MVC API routes
        return '/api/vehicles';
    },
    
    /**
     * Get available vehicles
     */
    async getAvailableVehicles(userLocation = null) {
        try {
            // Use MVC route
            let url = '/api/vehicles';
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.success && data.vehicles) {
                        return data.vehicles;
                    }
                }
            }
            
            // Return mock data for development
            return this.getMockVehicles();
        } catch (error) {
            return this.getMockVehicles();
        }
    },
    
    /**
     * Get nearby vehicles with filters
     */
    async getNearbyVehicles(lat, lng, filters = {}) {
        try {
            // Use the same endpoint - filtering can be done client-side or add query params
            let url = '/api/vehicles';
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.success && data.vehicles) {
                        return data.vehicles;
                    }
                }
            }
            
            return [];
        } catch (error) {
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
            const response = await fetch(`/api/vehicles?action=details&id=${vehicleId}`, {
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
            return null;
        }
    },
    
    /**
     * Claim a vehicle
     */
    async claimVehicle(vehicleId, duration = 30) {
        try {
            const response = await fetch('/api/vehicles/claim', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    vehicle_id: vehicleId,
                    duration: duration
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
           
            // Si la API devuelve success pero no vehicle, intentar construirlo desde booking
            if (data.success && !data.vehicle && data.booking) {
                data.vehicle = {
                    id: data.booking.vehicle_id,
                    license_plate: data.booking.license_plate,
                    brand: data.booking.brand,
                    model: data.booking.model,
                    battery: data.booking.battery || 85,
                    latitude: data.booking.latitude,
                    longitude: data.booking.longitude,
                    status: 'in_use',
                    booking_id: data.booking.id || data.booking_id,
                    booking_start: data.booking.start_datetime,
                    price_per_minute: data.booking.price_per_minute || '0.38'
                };
            }
            
            if (data.success && data.vehicle) {
                this.currentVehicle = data.vehicle;
                
                // Asegurar que tiene todos los campos necesarios
                if (!data.vehicle.id || !data.vehicle.license_plate) {
                    return {
                        success: false,
                        message: 'Vehicle data incomplete'
                    };
                }
                
                // Guardar en localStorage
                try {
                    const vehicleJson = JSON.stringify(data.vehicle);
                 
                    localStorage.setItem('currentVehicle', vehicleJson);
                    
                    // Verificar que se guardó correctamente
                    const saved = localStorage.getItem('currentVehicle');
                    
                    if (!saved || saved === 'undefined' || saved === 'null') {
                        return {
                            success: false,
                            message: 'Failed to save vehicle to localStorage'
                        };
                    }
                    
                } catch (e) {
                    return {
                        success: false,
                        message: 'localStorage error: ' + e.message
                    };
                }
                
                // Redirigir a la página de administrar vehículo
                setTimeout(() => {
                    window.location.href = '/administrar-vehicle';
                }, 1000);
                
                return { success: true, vehicle: data.vehicle };
            } else {
                const errorMsg = data.message || 'Error en reclamar el vehicle';
                return { success: false, message: errorMsg };
            }
        } catch (error) {
            return { success: false, error: error.message };
        }
    },
    
    /**
     * Release current vehicle
     */
    async releaseVehicle() {
        try {
            const response = await fetch('/api/vehicles/release', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({})
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentVehicle = null;
                localStorage.removeItem('currentVehicle');
                return { success: true, message: data.message };
            } else {
                return { success: false, message: data.message };
            }
        } catch (error) {
            return { success: false, message: error.message };
        }
    },
    
    /**
     * Get current vehicle
     */
    getCurrentVehicle() {
        if (!this.currentVehicle) {
            // Try to load from localStorage
            try {
                const stored = localStorage.getItem('currentVehicle');
                if (stored) {
                    this.currentVehicle = JSON.parse(stored);
                }
            } catch (e) {
            }
        }
        return this.currentVehicle;
    },
    
    /**
     * Control vehicle - activate horn
     */
    async activateHorn() {
        try {
            const response = await fetch('/api/vehicles/horn', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Removed Utils.showToast
                return { success: true };
            } else {
                // Removed Utils.showToast
                return { success: false };
            }
        } catch (error) {
            // Removed Utils.showToast
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - activate lights
     */
    async activateLights() {
        try {
            const response = await fetch('/api/vehicles/lights', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Removed Utils.showToast
                return { success: true };
            } else {
                // Removed Utils.showToast
                return { success: false };
            }
        } catch (error) {
            // Removed Utils.showToast
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - start engine
     */
    async startEngine() {
        try {
            
            const response = await fetch('/api/vehicles/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Removed Utils.showToast
                return { success: true };
            } else {
                // Removed Utils.showToast
                return { success: false };
            }
        } catch (error) {
            // Removed Utils.showToast
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - stop engine
     */
    async stopEngine() {
        try {
            const response = await fetch('/api/vehicles/stop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Removed Utils.showToast
                return { success: true };
            } else {
                // Removed Utils.showToast
                return { success: false };
            }
        } catch (error) {
            // Removed Utils.showToast
            return { success: false };
        }
    },
    
    /**
     * Control vehicle - lock/unlock doors
     */
    async toggleDoors(lock = true) {
        try {
            const endpoint = lock ? '/api/vehicles/lock' : '/api/vehicles/unlock';
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({})
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Removed Utils.showToast
                return { success: true };
            } else {
                // Removed Utils.showToast
                return { success: false };
            }
        } catch (error) {
            // Removed Utils.showToast
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
