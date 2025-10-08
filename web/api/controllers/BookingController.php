<?php
/**
 * Booking Controller
 * Handles booking management endpoints
 * Implements 2025 RBAC best practices
 */

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Response.php';

class BookingController {
    private $bookingModel;
    private $vehicleModel;
    
    public function __construct() {
        $this->bookingModel = new Booking();
        $this->vehicleModel = new Vehicle();
    }
    
    /**
     * Get all bookings
     * GET /api/bookings
     */
    public function index() {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get query parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
            $vehicleId = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            
            // Validate pagination
            $page = max(1, $page);
            $perPage = min(100, max(1, $perPage));
            
            // Validate status if provided
            if ($status && !in_array($status, ['active', 'completed', 'cancelled'])) {
                Response::badRequest('Invalid status value');
                return;
            }
            
            // Regular users can only see their own bookings
            if (!isAdmin($currentUser) && !$userId) {
                $userId = $currentUser['id'];
            }
            
            // Get bookings
            if ($userId) {
                // Check permission
                if (!canAccessUserResource($currentUser, $userId)) {
                    Response::forbidden('You do not have permission to access these bookings');
                    return;
                }
                
                $bookings = $this->bookingModel->findByUser($userId, $page, $perPage);
                $total = $this->bookingModel->count($userId);
            } elseif ($vehicleId) {
                // Admin only
                if (!isAdmin($currentUser)) {
                    Response::forbidden('Only administrators can view bookings by vehicle');
                    return;
                }
                
                $bookings = $this->bookingModel->findByVehicle($vehicleId, $page, $perPage);
                $total = $this->bookingModel->count();
            } elseif ($status === 'active') {
                $bookings = $this->bookingModel->getActive($page, $perPage);
                $total = $this->bookingModel->count();
            } else {
                // Admin only
                if (!isAdmin($currentUser)) {
                    Response::forbidden('Only administrators can view all bookings');
                    return;
                }
                
                $bookings = $this->bookingModel->getAll($page, $perPage);
                $total = $this->bookingModel->count();
            }
            
            Response::paginated($bookings, $total, $page, $perPage, 'Bookings retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get bookings error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving bookings');
        }
    }
    
    /**
     * Get booking by ID
     * GET /api/bookings/{id}
     */
    public function show($id) {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get booking
            $booking = $this->bookingModel->findById($id);
            
            if (!$booking) {
                Response::notFound('Booking not found');
                return;
            }
            
            // Check permission
            if (!canAccessUserResource($currentUser, $booking['user_id'])) {
                Response::forbidden('You do not have permission to access this booking');
                return;
            }
            
            Response::success($booking, 'Booking retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Get booking error: " . $e->getMessage());
            Response::serverError('An error occurred while retrieving booking');
        }
    }
    
    /**
     * Create booking
     * POST /api/bookings
     */
    public function create() {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Validate input
            $validation = Validator::validate($data, [
                'vehicle_id' => ['required' => true, 'type' => 'integer', 'min' => 1],
                'start_time' => ['required' => true, 'type' => 'datetime'],
                'end_time' => ['required' => false, 'type' => 'datetime']
            ]);
            
            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }
            
            // Check if vehicle exists
            $vehicle = $this->vehicleModel->findById($validation['data']['vehicle_id']);
            
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            // Check if vehicle is available
            if ($vehicle['status'] !== 'available') {
                Response::badRequest('Vehicle is not available for booking');
                return;
            }
            
            // Check if vehicle is available for the time period
            if (isset($validation['data']['end_time'])) {
                $isAvailable = $this->bookingModel->isVehicleAvailable(
                    $validation['data']['vehicle_id'],
                    $validation['data']['start_time'],
                    $validation['data']['end_time']
                );
                
                if (!$isAvailable) {
                    Response::conflict('Vehicle is already booked for this time period');
                    return;
                }
            }
            
            // Create booking
            $bookingData = [
                'user_id' => $currentUser['id'],
                'vehicle_id' => $validation['data']['vehicle_id'],
                'start_time' => $validation['data']['start_time'],
                'end_time' => $validation['data']['end_time'] ?? null,
                'status' => 'active',
                'price_per_hour' => $vehicle['price_per_hour']
            ];
            
            $bookingId = $this->bookingModel->create($bookingData);
            
            if (!$bookingId) {
                Response::serverError('Failed to create booking');
                return;
            }
            
            // Update vehicle status to in_use
            $this->vehicleModel->updateStatus($validation['data']['vehicle_id'], 'in_use');
            
            // Get created booking
            $booking = $this->bookingModel->findById($bookingId);
            
            // Log creation
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => "Booking created for vehicle {$vehicle['license_plate']}",
                'user_id' => $currentUser['id'],
                'action' => 'create_booking',
                'ip_address' => getClientIP()
            ]);
            
            Response::created($booking, 'Booking created successfully');
            
        } catch (Exception $e) {
            error_log("Create booking error: " . $e->getMessage());
            Response::serverError('An error occurred while creating booking');
        }
    }
    
    /**
     * Update booking
     * PUT /api/bookings/{id}
     */
    public function update($id) {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get booking
            $booking = $this->bookingModel->findById($id);
            
            if (!$booking) {
                Response::notFound('Booking not found');
                return;
            }
            
            // Check permission
            if (!canAccessUserResource($currentUser, $booking['user_id'])) {
                Response::forbidden('You do not have permission to update this booking');
                return;
            }
            
            // Get request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                Response::badRequest('Invalid JSON data');
                return;
            }
            
            // Build validation rules
            $rules = [];
            
            if (isset($data['start_time'])) {
                $rules['start_time'] = ['required' => true, 'type' => 'datetime'];
            }
            
            if (isset($data['end_time'])) {
                $rules['end_time'] = ['required' => true, 'type' => 'datetime'];
            }
            
            if (isset($data['status'])) {
                $rules['status'] = ['required' => true, 'type' => 'enum', 'values' => ['active', 'completed', 'cancelled']];
            }
            
            // Validate input
            if (!empty($rules)) {
                $validation = Validator::validate($data, $rules);
                
                if (!$validation['valid']) {
                    Response::validationError($validation['errors']);
                    return;
                }
                
                // Update booking
                $success = $this->bookingModel->update($id, $validation['data']);
                
                if (!$success) {
                    Response::serverError('Failed to update booking');
                    return;
                }
            }
            
            // Get updated booking
            $updatedBooking = $this->bookingModel->findById($id);
            
            Response::success($updatedBooking, 'Booking updated successfully');
            
        } catch (Exception $e) {
            error_log("Update booking error: " . $e->getMessage());
            Response::serverError('An error occurred while updating booking');
        }
    }
    
    /**
     * Complete booking
     * POST /api/bookings/{id}/complete
     */
    public function complete($id) {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get booking
            $booking = $this->bookingModel->findById($id);
            
            if (!$booking) {
                Response::notFound('Booking not found');
                return;
            }
            
            // Check permission
            if (!canAccessUserResource($currentUser, $booking['user_id'])) {
                Response::forbidden('You do not have permission to complete this booking');
                return;
            }
            
            // Check if booking is active
            if ($booking['status'] !== 'active') {
                Response::badRequest('Only active bookings can be completed');
                return;
            }
            
            // Get vehicle to get price
            $vehicle = $this->vehicleModel->findById($booking['vehicle_id']);
            
            if (!$vehicle) {
                Response::notFound('Vehicle not found');
                return;
            }
            
            // Complete booking
            $endTime = date('Y-m-d H:i:s');
            $success = $this->bookingModel->complete($id, $endTime, $vehicle['price_per_hour']);
            
            if (!$success) {
                Response::serverError('Failed to complete booking');
                return;
            }
            
            // Update vehicle status to available
            $this->vehicleModel->updateStatus($booking['vehicle_id'], 'available');
            
            // Get updated booking
            $updatedBooking = $this->bookingModel->findById($id);
            
            // Log completion
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => "Booking completed for vehicle {$vehicle['license_plate']}",
                'user_id' => $currentUser['id'],
                'action' => 'complete_booking',
                'ip_address' => getClientIP()
            ]);
            
            Response::success($updatedBooking, 'Booking completed successfully');
            
        } catch (Exception $e) {
            error_log("Complete booking error: " . $e->getMessage());
            Response::serverError('An error occurred while completing booking');
        }
    }
    
    /**
     * Cancel booking
     * POST /api/bookings/{id}/cancel
     */
    public function cancel($id) {
        try {
            // Require authentication
            $currentUser = authenticate();
            
            // Get booking
            $booking = $this->bookingModel->findById($id);
            
            if (!$booking) {
                Response::notFound('Booking not found');
                return;
            }
            
            // Check permission
            if (!canAccessUserResource($currentUser, $booking['user_id'])) {
                Response::forbidden('You do not have permission to cancel this booking');
                return;
            }
            
            // Check if booking is active
            if ($booking['status'] !== 'active') {
                Response::badRequest('Only active bookings can be cancelled');
                return;
            }
            
            // Cancel booking
            $success = $this->bookingModel->cancel($id);
            
            if (!$success) {
                Response::serverError('Failed to cancel booking');
                return;
            }
            
            // Update vehicle status to available
            $this->vehicleModel->updateStatus($booking['vehicle_id'], 'available');
            
            // Get updated booking
            $updatedBooking = $this->bookingModel->findById($id);
            
            // Log cancellation
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'info',
                'message' => "Booking cancelled for vehicle ID {$booking['vehicle_id']}",
                'user_id' => $currentUser['id'],
                'action' => 'cancel_booking',
                'ip_address' => getClientIP()
            ]);
            
            Response::success($updatedBooking, 'Booking cancelled successfully');
            
        } catch (Exception $e) {
            error_log("Cancel booking error: " . $e->getMessage());
            Response::serverError('An error occurred while cancelling booking');
        }
    }
    
    /**
     * Delete booking (admin only)
     * DELETE /api/bookings/{id}
     */
    public function delete($id) {
        try {
            // Require admin role
            $currentUser = requireAdmin();
            
            // Get booking
            $booking = $this->bookingModel->findById($id);
            
            if (!$booking) {
                Response::notFound('Booking not found');
                return;
            }
            
            // Delete booking
            $success = $this->bookingModel->delete($id);
            
            if (!$success) {
                Response::serverError('Failed to delete booking');
                return;
            }
            
            // Log deletion
            require_once __DIR__ . '/../models/Sensor.php';
            $sensorModel = new Sensor();
            
            $sensorModel->insertLog([
                'level' => 'warning',
                'message' => "Booking deleted: ID {$id}",
                'user_id' => $currentUser['id'],
                'action' => 'delete_booking',
                'ip_address' => getClientIP()
            ]);
            
            Response::success(null, 'Booking deleted successfully');
            
        } catch (Exception $e) {
            error_log("Delete booking error: " . $e->getMessage());
            Response::serverError('An error occurred while deleting booking');
        }
    }
}
