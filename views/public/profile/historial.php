<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('profile.trip_history_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .phone-frame {
            border: 12px solid #212121;
            border-radius: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            padding: 10px;
            background-color: #212121;
        }
        /* Hide the scrollbar for a cleaner appearance */
*/
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-sm md:max-w-3xl lg:max-w-4xl h-[667px] md:h-auto flex items-center justify-center">
        <div class="bg-white p-5 rounded-2xl shadow-inner w-full h-full flex flex-col relative space-y-6">
            <header class="grid grid-cols-3 items-center mb-6 w-full">
                <div class="text-left">
                    <a href="/profile" class="text-[#1565C0] text-sm font-semibold"><?php echo __('profile.back_to_profile'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('profile.trip_history_title'); ?></h1>
                <div class="flex justify-end">
                    <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
                </div>
            </header>
            
            <main class="flex-1 overflow-y-auto no-scrollbar mt-3">
                <?php if (empty($history)): ?>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Encara no tens viatges</h3>
                        <p class="text-gray-500 text-sm">Quan reclamis el teu primer vehicle, els viatges apareixeran aquí.</p>
                        <a href="/dashboard" class="inline-block mt-4 bg-[#1565C0] text-white px-6 py-2 rounded-lg hover:bg-[#0d47a1] transition">
                            Localitzar Vehicles
                        </a>
                    </div>
                <?php else: ?>
                    <ul class="space-y-4">
                        <?php foreach ($history as $index => $trip): ?>
                            <li class="bg-[#F5F5F5] p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-bold text-lg text-gray-900">
                                                <?php echo htmlspecialchars($trip['vehicle_brand'] . ' ' . $trip['vehicle_model']); ?>
                                            </h3>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                <?php echo htmlspecialchars($trip['vehicle_plate']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="space-y-1 text-sm text-gray-700">
                                            <p>
                                                <svg class="inline h-4 w-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <strong>Data:</strong> 
                                                <?php echo date('d/m/Y H:i', strtotime($trip['start_time'])); ?>
                                            </p>
                                            
                                            <?php if ($trip['end_time']): ?>
                                                <p>
                                                    <svg class="inline h-4 w-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <strong>Durada:</strong> 
                                                    <?php echo htmlspecialchars($trip['duration_minutes'] ?? 0); ?> min
                                                </p>
                                            <?php else: ?>
                                                <p class="text-orange-600">
                                                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <strong>En curs</strong>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($trip['total_distance_km']): ?>
                                                <p>
                                                    <svg class="inline h-4 w-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                                    </svg>
                                                    <strong>Distància:</strong> 
                                                    <?php echo number_format($trip['total_distance_km'], 2); ?> km
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($trip['start_location_name']): ?>
                                                <p class="text-xs text-gray-600 mt-2">
                                                    <svg class="inline h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Origen: <?php echo htmlspecialchars($trip['start_location_name']); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if ($trip['end_location_name']): ?>
                                                <p class="text-xs text-gray-600">
                                                    <svg class="inline h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Destí: <?php echo htmlspecialchars($trip['end_location_name']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($trip['vehicle_image']): ?>
                                        <div class="w-24 h-24 flex-shrink-0">
                                            <img src="<?php echo htmlspecialchars($trip['vehicle_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($trip['vehicle_brand']); ?>" 
                                                 class="w-full h-full object-cover rounded-md">
                                        </div>
                                    <?php else: ?>
                                        <div class="w-24 h-24 bg-gray-300 rounded-md flex items-center justify-center flex-shrink-0">
                                            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </main>
        </div>
    </div>
  <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>