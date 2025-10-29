<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMS - Comprar Temps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/toast.js" defer></script>
    <link rel="stylesheet" href="../../css/custom.css">

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div
        class="p-5 rounded-2xl shadow-inner bg-white w-full max-w-sm md:max-w-3xl lg:max-w-4xl flex flex-col relative space-y-6">
        <header class="grid grid-cols-3 items-center mb-6 w-full">
            <div class="text-left">
                <a href="/dashboard" class="text-[#1565C0] font-semibold hover:underline">← Tornar</a>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 text-center">Comprar Temps</h1>
            <div class="flex justify-end">
                <div class="rounded-full flex items-center justify-center">
                    <img src="/assets/images/logo.png" alt="Logo App" class="h-12 w-12" />
                </div>
            </div>
        </header>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <main class="flex-1 overflow-y-auto no-scrollbar space-y-6 mt-3">
            <div class="flex-1 md:flex md:flex-col md:justify-start space-y-6 md:space-y-0">

                <div class="w-full text-center">
                    <a href="/premium" role="button" aria-label="Passa a Premium" tabindex="0"
                        class="relative w-full block mt-3 md:mt-6 mb-1 md:mb-0 cursor-pointer outline-none transition-[filter,transform] duration-250 hover:brightness-110">
                        <span
                            class="absolute top-0 left-0 w-full h-full rounded-xl bg-yellow-700 transform translate-y-0.5 transition-transform duration-[600ms] ease-[cubic-bezier(.3,.7,.4,1)]"
                            aria-hidden="true"></span>

                        <span
                            class="absolute top-0 left-0 w-full h-full rounded-xl bg-gradient-to-l from-yellow-600 via-yellow-500 to-yellow-600"
                            aria-hidden="true"></span>

                        <span
                            class="relative block px-6 py-4 rounded-xl bg-yellow-500 text-yellow-900 font-bold transform -translate-y-1.5 transition-transform duration-[600ms] ease-[cubic-bezier(.3,.7,.4,1)] text-shadow-[1px_1px_2px_rgba(255,255,255,0.4)]">
                            <span class="block text-sm">Passa a</span>
                            <span class="block text-2xl">Premium</span>
                            <span class="block text-sm">9,99€ / mes</span>
                        </span>

                    </a>
                </div>


                <div class="w-full text-center">
                    <p class="text-gray-600 text-center text-sm mb-1 md:mb-1">O selecciona una opció de temps:</p>
                </div>
            </div>

            <div class="flex-1 md:flex md:flex-col md:justify-start space-y-6 md:space-y-0">
                <div class="w-full bg-yellow-100 p-3 rounded-lg flex items-center shadow-sm mb-1 md:mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 mr-3 flex-shrink-0"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938-7a9 9 0 1113.876 0l-1.416 2.45a7 7 0 10-10.884 0L5.062 7z" />
                    </svg>
                    <p class="text-xs text-gray-700">Recorda: cada viatge té un cost de desbloqueig de **0,50€**.</p>
                </div>

                <ul class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
                    <div onclick="openModal(event)" data-minutes="10" data-price="1.50"
                        class="block p-4 rounded-lg shadow-md flex-col md:flex-row justify-between items-center md:space-x-4 transition-all duration-300 transform hover:scale-105 mb-1 md:mb-0"
                        style="background: linear-gradient(135deg, #f0f4f8, #e0e8f0);">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600 mr-4 flex-shrink-0"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <p class="font-bold text-lg text-gray-800 mb-1 md:mb-0">10 minuts</p>
                                <p class="text-gray-600 text-sm mb-1 md:mb-0">Per a viatges curts i urgents.</p>
                            </div>
                        </div>
                        <span class="font-bold text-2xl text-gray-900">1,50€</span>
                    </div>
                    <div onclick="openModal(event)" data-minutes="30" data-price="4.00"
                        class="block p-4 rounded-lg shadow-md flex-col md:flex-row justify-between items-center md:space-x-4 transition-all duration-300 transform hover:scale-105 mb-1 md:mb-0"
                        style="background: linear-gradient(135deg, #e6f7ff, #b3e0ff);">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mr-4 flex-shrink-0"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <p class="font-bold text-lg text-blue-900 mb-1 md:mb-0">30 minuts</p>
                                <p class="text-blue-700 text-sm mb-1 md:mb-0">Per a desplaçaments mitjans.</p>
                            </div>
                        </div>
                        <span class="font-bold text-2xl text-blue-900">4,00€</span>
                    </div>
                    <div onclick="openModal(event)" data-minutes="60" data-price="7.50"
                        class="block p-4 rounded-lg shadow-md flex-col md:flex-row justify-between items-center md:space-x-4 transition-all duration-300 transform hover:scale-105 mb-1 md:mb-0"
                        style="background: linear-gradient(135deg, #e6ffec, #b3ffc7);">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 mr-4 flex-shrink-0"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <p class="font-bold text-lg text-green-900 mb-1 md:mb-0">60 minuts</p>
                                <p class="text-green-700 text-sm mb-1 md:mb-0">Ideal per a viatges més llargs.</p>
                            </div>
                        </div>
                        <span class="font-bold text-2xl text-green-900">7,50€</span>
                    </div>
                    <div onclick="openModal(event)" data-minutes="120" data-price="14.00"
                        class="block p-4 rounded-lg shadow-md flex-col md:flex-row justify-between items-center md:space-x-4 transition-all duration-300 transform hover:scale-105 mb-1 md:mb-0"
                        style="background: linear-gradient(135deg, #fff3e6, #ffe0b3);">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600 mr-4 flex-shrink-0"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <p class="font-bold text-lg text-yellow-900 mb-1 md:mb-0">120 minuts</p>
                                <p class="text-yellow-700 text-sm mb-1 md:mb-0">Dos hores completes de viatge.</p>
                            </div>
                        </div>
                        <span class="font-bold text-2xl text-yellow-900">14,00€</span>
                    </div>
                    <div onclick="openModal(event)" data-minutes="180" data-price="20.00"
                        class="block p-4 rounded-lg shadow-md flex-col md:flex-row justify-between items-center md:space-x-4 transition-all duration-300 transform hover:scale-105 mb-1 md:mb-0"
                        style="background: linear-gradient(135deg, #ffe0e6, #ffb3bf);">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600 mr-4 flex-shrink-0"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <p class="font-bold text-lg text-red-900 mb-1 md:mb-0">180 minuts</p>
                                <p class="text-red-700 text-sm mb-1 md:mb-0">Per a grans rutes o turisme.</p>
                            </div>
                        </div>
                        <span class="font-bold text-2xl text-red-900">20,00€</span>
                    </div>
                    <div onclick="openModal(event)" data-minutes="240" data-price="25.00"
                        class="block p-4 rounded-lg shadow-md flex-col md:flex-row justify-between items-center md:space-x-4 transition-all duration-300 transform hover:scale-105 mb-1 md:mb-0"
                        style="background: linear-gradient(135deg, #e6e6ff, #b3b3ff);">

                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 mr-4 flex-shrink-0"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div>
                                <p class="font-bold text-lg text-indigo-900 mb-1 md:mb-0">240 minuts</p>
                                <p class="text-indigo-700 text-sm mb-1 md:mb-0">Un matí o tarda sencera.</p>
                            </div>
                        </div>
                        <span class="font-bold text-2xl text-indigo-900">25,00€</span>
                    </div>
                </ul>
            </div>
        </main>

        </a>
    </div>

    <div id="purchaseModal" class="hidden w-full fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-80 text-center">
            <h2 class="text-xl font-semibold mb-4 text-blue-800">Confirmar compra</h2>
            <p id="modalText" class="text-gray-700 mb-6">¿Deseas confirmar esta compra?</p>
            <div class="flex justify-around">
                <button onclick="confirmPurchase()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Confirmar</button>
                <button onclick="closeModal()"
                    class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400">Cancelar</button>
            </div>
        </div>
    </div>

</body>

<script>
    // Solo animaciones de modal
    let minutesSelected = 0;
    let priceSelected = 0;

    function openModal(e) {
        const target = e.currentTarget;
        minutesSelected = parseInt(target.dataset.minutes);
        priceSelected = parseFloat(target.dataset.price);

        const modalText = document.querySelector('#purchaseModal #modalText');
        modalText.textContent = `Vols comprar ${minutesSelected} minuts per ${priceSelected.toFixed(2)}€?`;

        const modal = document.getElementById('purchaseModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('purchaseModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function confirmPurchase() {
        // Crear formulario HTML y enviarlo
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/purchase-time';
        
        const minutesInput = document.createElement('input');
        minutesInput.type = 'hidden';
        minutesInput.name = 'minutes';
        minutesInput.value = minutesSelected;
        
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = 'price';
        priceInput.value = priceSelected;
        
        form.appendChild(minutesInput);
        form.appendChild(priceInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>

</html>