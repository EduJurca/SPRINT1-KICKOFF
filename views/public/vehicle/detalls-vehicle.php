<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('details.page_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .phone-frame {
            border: 12px solid #212121;
            border-radius: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            padding: 10px;
            background-color: #212121;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-sm md:max-w-3xl lg:max-w-4xl h-[667px] md:h-auto flex items-center justify-center">
        <div class="bg-white p-5 rounded-2xl shadow-inner w-full h-full flex flex-col relative space-y-6">
            <header class="grid grid-cols-3 items-center mb-6 w-full">
                <div class="text-left">
                    <a href="/localitzar-vehicle" class="text-[#1565C0] text-sm font-semibold"><?php echo __('details.back'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('details.vehicle_details'); ?></h1>
                <div class="flex justify-end">
                    <img src="/assets/images/logo.png" alt="<?php echo __('details.logo_alt'); ?>" class="h-10 w-10">
                </div>
            </header>

            <div class="flex-1 overflow-y-auto no-scrollbar space-y-6 mt-3">
                <div class="bg-gray-200 h-48 w-full rounded-lg flex items-center justify-center mb-4 text-gray-600">
                    
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900"><?php echo __('details.general_information'); ?></h2>
                    <div class="bg-[#F5F5F5] p-4 rounded-lg">
                        <p class="text-gray-900 font-semibold text-lg"><?php echo __('details.license_plate'); ?> <span class="font-normal">AB 123 CD</span></p>
                        <p class="text-gray-700"><?php echo __('details.model'); ?> <span class="font-normal">Motocicleta Y</span></p>
                        <p class="text-gray-700"><?php echo __('details.status'); ?> <span class="font-normal text-[#00C853]"><?php echo __('details.operational'); ?></span></p>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900"><?php echo __('details.battery_status'); ?></h2>
                    <div class="bg-[#F5F5F5] p-4 rounded-lg">
                        <div class="w-full bg-gray-300 rounded-full h-6 overflow-hidden">
                            <div class="bg-[#00C853] h-6 text-white flex items-center justify-center font-bold text-sm" style="width: 95%;">95%</div>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900"><?php echo __('details.location'); ?></h2>
                    <div class="bg-gray-300 h-48 w-full rounded-lg flex items-center justify-center text-gray-600">
                        
                    </div>
                </div>
            </div>

            <a href="/localitzar-vehicle" class="mt-6 block w-full bg-[#1565C0] text-white font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300 text-center">
                <?php echo __('details.claim_this_vehicle'); ?>
            </a>
        </div>
    </div>
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    
</body>
</html>