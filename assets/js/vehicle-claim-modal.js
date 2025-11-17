const VehicleClaimModal = {
    modal: null,
    currentVehicle: null,
    unlockFee: 0.50,
    selectedDuration: 30,
    init() {
        this.modal = document.getElementById('claim-modal');
        this.setupEventListeners();
    },

    setupEventListeners() {
        if (!this.modal) {
            return;
        }
        
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        const closeBtn = document.getElementById('claim-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }
        
        const cancelBtn = document.getElementById('claim-modal-cancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.close());
        }
        
        const confirmBtn = document.getElementById('claim-modal-confirm');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.confirm());
        }

        const durationSelect = document.getElementById('rental-duration');
        if (durationSelect) {
            durationSelect.addEventListener('change', (e) => {
                this.selectedDuration = parseInt(e.target.value);
                this.updatePriceCalculation();
            });
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.close();
            }
        });
    },

    show(vehicle) {
        if (!this.modal) {
            return;
        }
        
        this.currentVehicle = vehicle;
        this.selectedDuration = 30;
        this.updateVehicleInfo(vehicle);
        this.updatePriceCalculation();
        
        const durationSelect = document.getElementById('rental-duration');
        if (durationSelect) {
            durationSelect.value = '30';
        }
        
        this.modal.style.display = 'flex';
        this.modal.style.opacity = '1';
        this.modal.style.visibility = 'visible';
        this.modal.classList.add('active');
        
        document.body.style.overflow = 'hidden';
    },

    updatePriceCalculation() {
        if (!this.currentVehicle) return;

        const pricePerMinute = parseFloat(this.currentVehicle.price_per_minute) || 0;
        const timeCost = pricePerMinute * this.selectedDuration;
        const totalCost = timeCost + this.unlockFee;

        const pricePerMinuteEl = document.getElementById('price-per-minute');
        const selectedDurationEl = document.getElementById('selected-duration');
        const timeCostEl = document.getElementById('time-cost');
        const totalCostEl = document.getElementById('total-cost');

        if (pricePerMinuteEl) {
            pricePerMinuteEl.textContent = `€${pricePerMinute.toFixed(2)}/min`;
        }

        if (selectedDurationEl) {
            const hours = Math.floor(this.selectedDuration / 60);
            const minutes = this.selectedDuration % 60;
            let durationText = '';
            
            if (hours > 0) {
                durationText += `${hours} ${hours === 1 ? 'hora' : 'hores'}`;
            }
            if (minutes > 0) {
                if (hours > 0) durationText += ' ';
                durationText += `${minutes} min`;
            }
            
            selectedDurationEl.textContent = durationText;
        }

        if (timeCostEl) {
            timeCostEl.textContent = `€${timeCost.toFixed(2)}`;
        }

        if (totalCostEl) {
            totalCostEl.textContent = `€${totalCost.toFixed(2)}`;
        }
    },

    updateVehicleInfo(vehicle) {
        const vehicleInfoContainer = document.getElementById('vehicle-info');
        
        const batteryColor = vehicle.battery >= 80 ? '#10B981' : 
                            vehicle.battery >= 50 ? '#F59E0B' : 
                            vehicle.battery >= 20 ? '#F97316' : '#EF4444';
        
        const infoHTML = `
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 text-sm">${(window.TRANSLATIONS && window.TRANSLATIONS['vehicle.model']) || 'Model:'}</span>
                    <span class="font-semibold text-gray-900">${vehicle.model}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 text-sm">${(window.TRANSLATIONS && window.TRANSLATIONS['vehicle.license_plate']) || 'License Plate:'}</span>
                    <span class="font-semibold text-gray-900">${vehicle.license_plate}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 text-sm">${(window.TRANSLATIONS && window.TRANSLATIONS['vehicle.battery']) || 'Battery:'}</span>
                    <span class="font-semibold" style="color: ${batteryColor};">
                        ${vehicle.battery}%
                    </span>
                </div>
                ${vehicle.distance ? `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">${(window.TRANSLATIONS && window.TRANSLATIONS['vehicle.distance']) || 'Distance:'}</span>
                        <span class="font-semibold text-gray-900">${vehicle.distance.toFixed(2)} km</span>
                    </div>
                ` : ''}
                ${vehicle.is_accessible ? `
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">${(window.TRANSLATIONS && window.TRANSLATIONS['vehicle.accessible_label']) || 'Accessible:'}</span>
                        <span class="font-semibold text-green-600">✓ Sí</span>
                    </div>
                ` : ''}
            </div>
        `;
        
        vehicleInfoContainer.innerHTML = infoHTML;
    },

    close() {
        if (!this.modal) return;
        
        this.modal.style.display = 'none';
        this.modal.style.opacity = '0';
        this.modal.style.visibility = 'hidden';
        this.modal.classList.remove('active');
        
        document.body.style.overflow = ''; 
        this.currentVehicle = null;
    },
    
    async confirm() {
        if (!this.currentVehicle) {
            showToast((window.TRANSLATIONS && window.TRANSLATIONS['vehicle.no_vehicle_selected']) || 'No vehicle selected', 'error');
            return;
        }
        
        const confirmBtn = document.getElementById('claim-modal-confirm');
        const cancelBtn = document.getElementById('claim-modal-cancel');
        
        confirmBtn.disabled = true;
        confirmBtn.classList.add('claim-modal-button-loading');
        cancelBtn.disabled = true;
        
        try {
            const result = await Vehicles.claimVehicle(
                this.currentVehicle.id, 
                this.selectedDuration
            );
            
            if (result.success) {
                this.close();
                
                showToast((window.TRANSLATIONS && window.TRANSLATIONS['vehicle.claimed_success']) || 'Vehicle claimed successfully! Redirecting...', 'success', 2000);
            } else {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('claim-modal-button-loading');
                cancelBtn.disabled = false;
                
                const errorMsg = result.message || result.error || (window.TRANSLATIONS && window.TRANSLATIONS['vehicle.claim_error_unknown']) || 'Unknown error while claiming the vehicle';
                
                showToast(errorMsg, 'error', 4000);
            }
        } catch (error) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('claim-modal-button-loading');
            cancelBtn.disabled = false;
            
            const errorMsg = error.message || (window.TRANSLATIONS && window.TRANSLATIONS['vehicle.claim_processing_error']) || 'Error processing claim';
            showToast(errorMsg, 'error', 4000);
        }
    }
};

window.showClaimModal = function(vehicle) {
    if (!VehicleClaimModal.modal) {
        VehicleClaimModal.init();
    }
    
    VehicleClaimModal.show(vehicle);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        VehicleClaimModal.init();
    });
} else {
    VehicleClaimModal.init();
}

window.VehicleClaimModal = VehicleClaimModal;

