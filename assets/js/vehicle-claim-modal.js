const VehicleClaimModal = {
    modal: null,
    currentVehicle: null,
    unlockFee: 0.50,
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
        this.updateVehicleInfo(vehicle);
        
        this.modal.style.display = 'flex';
        this.modal.style.opacity = '1';
        this.modal.style.visibility = 'visible';
        this.modal.classList.add('active');
        
        document.body.style.overflow = 'hidden';
    },

    updateVehicleInfo(vehicle) {
        const vehicleInfoContainer = document.getElementById('vehicle-info');
        
        const batteryColor = vehicle.battery >= 80 ? '#10B981' : 
                            vehicle.battery >= 50 ? '#F59E0B' : 
                            vehicle.battery >= 20 ? '#F97316' : '#EF4444';
        
        const infoHTML = `
            <div class="vehicle-info-row">
                <span class="vehicle-info-label">Model:</span>
                <span class="vehicle-info-value">${vehicle.model}</span>
            </div>
            <div class="vehicle-info-row">
                <span class="vehicle-info-label">Matrícula:</span>
                <span class="vehicle-info-value">${vehicle.license_plate}</span>
            </div>
            <div class="vehicle-info-row">
                <span class="vehicle-info-label">Bateria:</span>
                <span class="vehicle-info-value" style="color: ${batteryColor};">
                    ${vehicle.battery}%
                </span>
            </div>
            ${vehicle.distance ? `
                <div class="vehicle-info-row">
                    <span class="vehicle-info-label">Distància:</span>
                    <span class="vehicle-info-value">${vehicle.distance.toFixed(2)} km</span>
                </div>
            ` : ''}
            ${vehicle.is_accessible ? `
                <div class="vehicle-info-row">
                    <span class="vehicle-info-label">Accessible:</span>
                    <span class="vehicle-info-value">✓ Sí</span>
                </div>
            ` : ''}
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
            showToast('Error: No hay vehículo seleccionado', 'error');
            return;
        }
        
        const confirmBtn = document.getElementById('claim-modal-confirm');
        const cancelBtn = document.getElementById('claim-modal-cancel');
        
        confirmBtn.disabled = true;
        confirmBtn.classList.add('claim-modal-button-loading');
        cancelBtn.disabled = true;
        
        try {
            const result = await Vehicles.claimVehicle(this.currentVehicle.id);
            
            if (result.success) {
                this.close();
                
                showToast('Vehicle reclamat amb èxit! Redirigint...', 'success', 2000);
            } else {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('claim-modal-button-loading');
                cancelBtn.disabled = false;
                
                const errorMsg = result.message || result.error || 'Error desconegut al reclamar el vehicle';
                
                showToast(`Error: ${errorMsg}`, 'error');
            }
        } catch (error) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('claim-modal-button-loading');
            cancelBtn.disabled = false;
            
            const errorMsg = error.message || 'Error al procesar la reclamació';
            showToast(`❌ Error: ${errorMsg}`, 'error');
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

