<!DOCTYPE html>
<html lang="ca">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VoltiaCar - Gestió</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="/assets/js/toast.js"></script>

  <style>
    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }

    .no-scrollbar {
      -ms-overflow-style: none;
      /* IE i Edge */
      scrollbar-width: none;
      /* Firefox */
    }

    .customTooltip {
      background-color: #ffffff;
      color: #333333;
      font-size: 14px;
      line-height: 1.4;
      padding: 12px 16px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .customTooltip .introjs-tooltip-title {
      font-weight: bold;
      color: #1565C0;
      margin-bottom: 8px;
    }

    .customTooltip .introjs-tooltipbuttons {
      text-align: right;
    }

    .customTooltip .introjs-button {
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 13px;
    }
  </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

  <div class="bg-white p-8 rounded-2xl shadow-inner w-full max-w-sm flex flex-col relative overflow-visible md:hidden">

    <img src="/assets/images/logo.png" alt="Logo App" class="absolute top-4 left-4 h-10 w-10 z-20">

    <h1 class="text-2xl font-bold text-center text-gray-900 mb-8 mt-6">
      <?php echo __('dashboard.management_window'); ?>
    </h1>

    <main class="flex-1 overflow-y-auto no-scrollbar flex flex-col justify-between">

      <div>
        <div class="bg-gray-100 p-4 rounded-lg text-center shadow-sm mb-6">
          <p class="text-gray-700 font-semibold text-lg"><?php echo __('dashboard.available_time'); ?></p>
          <p id="minutes" class="text-4xl font-bold text-[#1565C0] mt-1"><?php echo htmlspecialchars($minute_balance ?? 0); ?> <?php echo __('dashboard.minutes'); ?></p>
        </div>

        <div class="grid grid-cols-2 gap-4 px-2 sm:px-4">

        <!-- CARDS FOR MOBILE -->
          <a href="/administrar-vehicle"
            class="bg-gray-100 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
            ria-label="Control Vehicle">
            <img src="/assets/images/control-vehicle.png" alt="Control Vehicle" class="h-12 w-12 mb-2">
            <p class="font-bold text-base text-gray-900"><?php echo __('dashboard.control_vehicle'); ?></p>
          </a>

          <a href="/localitzar-vehicle"
            class=" bg-gray-100 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
            aria-label="Reclamar Vehicle">
            <img src="/assets/images/reclamar-vehicle.png" alt="Reclamar Vehicle" class="h-12 w-12 mb-2">
            <p class="font-bold text-base text-gray-900"><?php echo __('dashboard.locate_vehicles'); ?></p>
          </a>

          <a href="/purchase-time"
            class=" bg-gray-100 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
            aria-label="Comprar Temps">
            <img src="/assets/images/comprar-temps.png" alt="Comprar Temps" class="h-12 w-12 mb-2">
            <p class="font-bold text-base text-gray-900"><?php echo __('dashboard.buy_time'); ?></p>
          </a>

          <a href="/perfil"
            class=" bg-gray-100 p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
            aria-label="Perfil">
            <img src="/assets/images/perfil.png" alt="Perfil" class="h-12 w-12 mb-2">
            <p class="font-bold text-base text-gray-900"><?php echo __('dashboard.profile'); ?></p>
          </a>

        </div>
      </div>

      <div class="mt-8">
        <form method="POST" action="/logout">
          <button type="submit"
            class="block w-full bg-gray-300 text-gray-900 font-semibold py-3 px-6 rounded-lg hover:bg-gray-400 transition-colors duration-300 text-center">
            <?php echo __('dashboard.logout'); ?>
          </button>
        </form>
      </div>
    </main>

    <div class="absolute top-4 right-4 z-10 flex gap-2">
      <button id="tutorialRestartBtn" data-tutorial-restart
        class="block bg-blue-100 p-2 rounded-full shadow-lg hover:bg-blue-200 transition-colors duration-300"
        aria-label="<?php echo __('dashboard.help_tutorial'); ?>" title="<?php echo __('dashboard.help_tutorial'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </button>
      <a href="/accessibilitat"
        class="block bg-gray-100 p-2 rounded-full shadow-lg hover:bg-gray-200 transition-colors duration-300"
        aria-label="<?php echo __('dashboard.accessibility_options'); ?>">
        <img src="/assets/images/accessibilitat.png" alt="Accessibilitat" class="h-7 w-7">
      </a>
    </div>

  </div>

  <div class="hidden md:flex bg-white p-8 rounded-2xl shadow-inner w-full max-w-6xl relative overflow-visible">
    <img src="/assets/images/logo.png" alt="Logo App" class="absolute top-4 left-4 h-10 w-10 z-20">

    <div class="absolute top-4 right-4 z-10 flex gap-2">
      <button id="tutorialRestartBtnDesktop" data-tutorial-restart
        class="block bg-blue-100 p-2 rounded-full shadow-lg hover:bg-blue-200 transition-colors duration-300"
        aria-label="<?php echo __('dashboard.help_tutorial'); ?>" title="<?php echo __('dashboard.help_tutorial'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </button>
      <a href="/accessibilitat"
        class="block bg-gray-100 p-2 rounded-full shadow-lg hover:bg-gray-200 transition-colors duration-300"
        aria-label="<?php echo __('dashboard.accessibility_options'); ?>">
        <img src="/assets/images/accessibilitat.png" alt="Accessibilitat" class="h-7 w-7">
      </a>
    </div>

    <section class="flex flex-col justify-between w-1/3 pr-12 border-r border-gray-200">
      <div>
        <h2 class="text-3xl font-bold text-gray-900 mb-8 mt-8">
          <?php echo __('dashboard.management_window'); ?>
        </h2>
        <div class="bg-gray-100 p-6 rounded-lg text-center shadow-sm mb-8">
          <p class="text-gray-700 font-semibold text-xl"><?php echo __('dashboard.available_time'); ?></p>
          <p id="minutesDesk" class="text-5xl font-bold text-[#1565C0] mt-2"><?php echo htmlspecialchars($minute_balance ?? 0); ?> <?php echo __('dashboard.minutes'); ?></p>
        </div>
      </div>
      <div>
        <form method="POST" action="/logout">
          <button type="submit"
            class="w-full bg-gray-300 text-gray-900 font-semibold py-4 px-8 rounded-lg hover:bg-gray-400 transition-colors duration-300 text-center">
            <?php echo __('dashboard.logout'); ?>
          </button>
        </form>
      </div>
    </section>

    <!-- CARDS FOR DESKTOP -->
    <section class="w-2/3 pl-12 grid grid-cols-2 gap-8 items-start">
      <a href="/administrar-vehicle"
        class=" bg-gray-100 p-6 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
        aria-label="Control Vehicle">
        <img src="/assets/images/control-vehicle.png" alt="Control Vehicle" class="h-16 w-16 mb-4">
        <p class="font-bold text-lg text-gray-900"><?php echo __('dashboard.control_vehicle'); ?></p>
      </a>

      <a href="/localitzar-vehicle"
        class=" bg-gray-100 p-6 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
        aria-label="Reclamar Vehicle">
        <img src="/assets/images/reclamar-vehicle.png" alt="Reclamar Vehicle" class="h-16 w-16 mb-4">
        <p class="font-bold text-lg text-gray-900"><?php echo __('dashboard.locate_vehicles'); ?></p>
      </a>

      <a href="/purchase-time"
        class=" bg-gray-100 p-6 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
        aria-label="Comprar Temps">
        <img src="/assets/images/comprar-temps.png" alt="Comprar Temps" class="h-16 w-16 mb-4">
        <p class="font-bold text-lg text-gray-900"><?php echo __('dashboard.buy_time'); ?></p>
      </a>

      <a href="/perfil"
        class=" bg-gray-100 p-6 rounded-lg shadow-sm flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105"
        aria-label="Perfil">
        <img src="/assets/images/perfil.png" alt="Perfil" class="h-16 w-16 mb-4">
        <p class="font-bold text-lg text-gray-900"><?php echo __('dashboard.profile'); ?></p>
      </a>
    </section>
  </div>

  <a href="/resum-projecte"
    class="fixed bottom-10 right-10 block bg-[#1565C0] text-white p-4 rounded-full shadow-lg hover:bg-[#1151a3] transition-colors duration-300 z-50"
    aria-label="<?php echo __('dashboard.project_summary'); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
      stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
  </a>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Animación: añadir efecto pulsante si no hay minutos
      const minutesText = document.querySelector('#minutes')?.textContent || '0 min';
      const minutesValue = parseInt(minutesText);
      const btnComprarTemps = document.querySelector('a[href="/purchase-time"]');
      
      if (minutesValue === 0 && btnComprarTemps) {
        btnComprarTemps.classList.add("animate-pulse", "ring-2", "ring-[#1565C0]");
      }
    });
  </script>

  <!-- Tutorial System -->
  <script src="/assets/js/tutorial.js"></script>

</body>
</html>