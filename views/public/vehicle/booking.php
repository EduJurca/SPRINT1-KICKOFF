<?php
/**
 * Vehicle Booking Page
 * Allows users to book a vehicle for a specific time period
 */

$pageTitle = __('booking.page_title');
$additionalCSS = [];

// Include header
include_once __DIR__ . '/../../php/components/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <a href="/dashboard" class="text-primary-blue hover:text-primary-blue-dark mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <?php echo __('booking.back'); ?>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">
                <?php echo __('booking.book_vehicle'); ?>
            </h1>
            <p class="text-gray-600 mt-2">
                <?php echo __('booking.select_dates_description'); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Vehicle Details Section -->
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <?php echo __('booking.vehicle_details'); ?>
                </h2>
                <div id="vehicle-details">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="animate-pulse">
                            <div class="h-48 bg-gray-200 rounded-lg mb-4"></div>
                            <div class="h-6 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>

                <!-- Cancellation Policy -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold text-blue-900 mb-2">
                        <?php echo __('booking.cancellation_policy'); ?>
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <?php echo __('booking.free_cancellation_24h'); ?></li>
                        <li>• <?php echo __('booking.full_refund_early_cancellation'); ?></li>
                        <li>• <?php echo __('booking.50_penalty_no_show'); ?></li>
                    </ul>
                </div>
            </div>

            <!-- Booking Form Section -->
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <?php echo __('booking.booking_details'); ?>
                </h2>
                
                <form id="booking-form" class="bg-white rounded-lg shadow-md p-6">
                    <!-- Start Date/Time -->
                    <div class="mb-6">
                        <label for="start_datetime" class="block text-sm font-semibold text-gray-700 mb-2">
                            <?php echo __('booking.start_date_time'); ?>
                        </label>
                        <input 
                            type="datetime-local" 
                            id="start_datetime" 
                            name="start_datetime"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            required
                        >
                    </div>

                    <!-- End Date/Time -->
                    <div class="mb-6">
                        <label for="end_datetime" class="block text-sm font-semibold text-gray-700 mb-2">
                            <?php echo __('booking.end_date_time'); ?>
                        </label>
                        <input 
                            type="datetime-local" 
                            id="end_datetime" 
                            name="end_datetime"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green"
                            required
                        >
                    </div>

                    <!-- Duration Display -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-semibold">
                                <?php echo __('booking.duration'); ?>
                            </span>
                            <span id="duration" class="text-gray-900 font-bold"><?php echo __('booking.duration_minutes'); ?></span>
                        </div>
                    </div>

                    <!-- Cost Breakdown -->
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h3 class="font-bold text-green-900 mb-3">
                            <?php echo __('booking.cost_summary'); ?>
                        </h3>
                        <div id="cost-breakdown">
                            <div class="text-sm text-gray-600 space-y-1">
                                <div class="flex justify-between">
                                    <span><?php echo __('booking.duration_label'); ?></span>
                                    <span><?php echo __('booking.duration_minutes_short'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span><?php echo __('booking.time_cost'); ?></span>
                                    <span>€0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span><?php echo __('booking.unlock_fee'); ?></span>
                                    <span>€0.50</span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-800 pt-2 border-t border-green-300">
                                    <span><?php echo __('booking.total'); ?></span>
                                    <span>€0.50</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cost Display -->
                    <div class="mb-6 p-4 bg-primary-green text-white rounded-lg text-center">
                        <div class="text-sm font-semibold mb-1">
                            <?php echo __('booking.total_cost'); ?>
                        </div>
                        <div id="total-cost" class="text-3xl font-bold">€0.00</div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-6">
                        <label class="flex items-start">
                            <input 
                                type="checkbox" 
                                id="accept_terms" 
                                name="accept_terms"
                                class="mt-1 mr-3 h-5 w-5 text-primary-green focus:ring-primary-green border-gray-300 rounded"
                                required
                            >
                                <span class="text-sm text-gray-700">
                                <?php echo __('booking.accept_terms_conditions'); ?>
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-primary-green hover:bg-primary-green-dark text-white font-bold py-4 rounded-lg transition-colors duration-300 shadow-lg hover:shadow-xl"
                    >
                        <?php echo __('booking.confirm_booking'); ?>
                    </button>
                </form>

                <!-- Additional Information -->
                <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-bold text-gray-900 mb-2">
                        <?php echo __('booking.important_information'); ?>
                    </h3>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• <?php echo __('booking.vehicle_reserved_selected_time'); ?></li>
                        <li>• <?php echo __('booking.payment_at_end'); ?></li>
                        <li>• <?php echo __('booking.arrive_on_time'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include booking.js -->
<script src="/assets/js/booking.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
