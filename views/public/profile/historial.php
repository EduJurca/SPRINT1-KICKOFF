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
                    <a href="/perfil" class="text-[#1565C0] text-sm font-semibold"><?php echo __('profile.back_to_profile'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('profile.trip_history_title'); ?></h1>
                <div class="flex justify-end">
                    <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
                </div>
            </header>
            
            <main class="flex-1 overflow-y-auto no-scrollbar mt-3">
                <ul class="space-y-6">
                    <li class="bg-[#F5F5F5] p-4 rounded-lg shadow-sm flex items-center justify-between space-x-4">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg mb-1"><?php echo __('profile.trip'); ?> 1</h3>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.date'); ?></strong> 15/09/2025</p>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.duration'); ?></strong> 25 <?php echo __('profile.minutes'); ?></p>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.vehicle_id'); ?></strong> T44-23</p>
                        </div>
                        <div class="w-1/2 h-24 bg-gray-300 rounded-md flex items-center justify-center text-gray-600 text-xs">
                            <?php echo __('profile.trip_route'); ?>
                        </div>
                    </li>
                    <li class="bg-[#F5F5F5] p-4 rounded-lg shadow-sm flex items-center justify-between space-x-4">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg mb-1"><?php echo __('profile.trip'); ?> 2</h3>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.date'); ?></strong> 12/09/2025</p>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.duration'); ?></strong> 15 <?php echo __('profile.minutes'); ?></p>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.vehicle_id'); ?></strong> T44-23</p>
                        </div>
                        <div class="w-1/2 h-24 bg-gray-300 rounded-md flex items-center justify-center text-gray-600 text-xs">
                            <?php echo __('profile.trip_route'); ?>
                        </div>
                    </li>
                    <li class="bg-[#F5F5F5] p-4 rounded-lg shadow-sm flex items-center justify-between space-x-4">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg mb-1"><?php echo __('profile.trip'); ?> 3</h3>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.date'); ?></strong> 09/09/2025</p>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.duration'); ?></strong> 40 <?php echo __('profile.minutes'); ?></p>
                            <p class="text-gray-700 text-sm"><strong><?php echo __('profile.vehicle_id'); ?></strong> T44-23</p>
                        </div>
                        <div class="w-1/2 h-24 bg-gray-300 rounded-md flex items-center justify-center text-gray-600 text-xs">
                            <?php echo __('profile.trip_route'); ?>
                        </div>
                    </li>
                </ul>
            </main>
        </div>
    </div>
</body>
</html>