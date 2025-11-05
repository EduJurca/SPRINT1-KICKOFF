<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('profile.payments_title'); ?></title>
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
                    <a href="/perfil" class="text-[#1565C0] text-sm font-semibold"><?php echo __('profile.back_to_profile'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('profile.payments_title'); ?></h1>
                <div class="flex justify-end">
                    <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
                </div>
            </header>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold mb-4 text-gray-900"><?php echo __('profile.current_cards'); ?></h2>

                <!-- Existing cards list -->
                <?php if (!empty($payment_methods)): ?>
                    <ul class="space-y-2 mb-4">
                        <?php foreach ($payment_methods as $method): ?>
                            <li class="bg-[#F5F5F5] p-3 rounded-lg shadow-sm flex justify-between items-center">
                                <div>
                                    <p class="text-gray-700 font-medium">
                                        <?php echo strtoupper($method['brand']); ?> **** **** **** <?php echo $method['last4']; ?>
                                        <?php if ($method['is_default']): ?>
                                            <span class="ml-2 text-xs bg-blue-500 text-white px-2 py-1 rounded"><?php echo __('profile.default_card'); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-gray-500 text-sm">
                                        <?php echo __('profile.expires'); ?>: <?php echo str_pad($method['exp_month'], 2, '0', STR_PAD_LEFT); ?>/<?php echo $method['exp_year']; ?>
                                    </p>
                                </div>
                                <button onclick="deleteCard(<?php echo $method['id']; ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                    <?php echo __('profile.delete'); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 mb-4"><?php echo __('profile.no_cards'); ?></p>
                <?php endif; ?>

                <!-- Add new card form -->
                <form id="add-card-form" action="/perfil/pagaments/add" method="POST" class="space-y-4" novalidate>
                    <div>
                        <label for="card-number" class="block text-sm font-medium text-gray-700"><?php echo __('profile.card_number') ?? 'Número de targeta'; ?></label>
                        <input id="card-number" name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="23" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2" required>
                        <p id="card-number-error" class="text-red-600 text-sm mt-1 hidden"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="expiry" class="block text-sm font-medium text-gray-700"><?php echo __('profile.card_expiry') ?? 'Data d\'expiració'; ?></label>
                            <!-- month input gives YYYY-MM value in most browsers -->
                            <input id="expiry" name="expiry" type="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2" required>
                            <p id="expiry-error" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>

                        <div>
                            <label for="cvc" class="block text-sm font-medium text-gray-700">CVC</label>
                            <input id="cvc" name="cvc" inputmode="numeric" autocomplete="cc-csc" placeholder="123" maxlength="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2" required>
                            <p id="cvc-error" class="text-red-600 text-sm mt-1 hidden"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between space-x-4">
                        <button type="submit" class="flex-1 bg-[#1565C0] text-white font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300"><?php echo __('profile.add_card') ?? 'Afegir targeta'; ?></button>
                        <!-- Example link/button to navigate to another page -->
                        <a href="/perfil/gestio" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-[#1565C0] bg-white hover:bg-gray-50">Anar a gestió</a>
                    </div>
                </form>

                <!-- Guidance: if you want to navigate programmatically, use JS window.location.href or set the form action to your server endpoint. See comments below. -->
            </div>
        </div>
    </div>

    <script>
        // Client-side validation for card form
        (function(){
            const form = document.getElementById('add-card-form');
            const cardNumberInput = document.getElementById('card-number');
            const expiryInput = document.getElementById('expiry');
            const cvcInput = document.getElementById('cvc');

            const cardNumberError = document.getElementById('card-number-error');
            const expiryError = document.getElementById('expiry-error');
            const cvcError = document.getElementById('cvc-error');

            function showError(el, msg){ el.textContent = msg; el.classList.remove('hidden'); }
            function clearError(el){ el.textContent = ''; el.classList.add('hidden'); }

            function luhnCheck(number){
                const digits = number.replace(/\D/g,'');
                if (digits.length < 12) return false; // too short to be a real card
                let sum = 0; let alt = false;
                for (let i = digits.length - 1; i >= 0; i--) {
                    let n = parseInt(digits.charAt(i), 10);
                    if (alt) { n *= 2; if (n > 9) n -= 9; }
                    sum += n; alt = !alt;
                }
                return sum % 10 === 0;
            }

            function expiryValid(value){
                if (!value) return false;
                // value from <input type=month> is like 'YYYY-MM'
                const parts = value.split('-');
                if (parts.length !== 2) return false;
                const year = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10);
                if (isNaN(year) || isNaN(month)) return false;

                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth() + 1; // 1-12

                // expiration should be the end of the month; require expiry >= current month
                if (year > currentYear) return true;
                if (year === currentYear && month >= currentMonth) return true;
                return false;
            }

            function cvcValid(value){
                return /^\d{3,4}$/.test(value);
            }

            form.addEventListener('submit', function(e){
                // clear previous errors
                clearError(cardNumberError); clearError(expiryError); clearError(cvcError);

                const cardVal = cardNumberInput.value.trim();
                const expiryVal = expiryInput.value;
                const cvcVal = cvcInput.value.trim();

                let ok = true;

                if (!luhnCheck(cardVal)){
                    showError(cardNumberError, 'Número de targeta invàlid (comprova el número o prova una altra targeta).');
                    ok = false;
                }

                if (!expiryValid(expiryVal)){
                    showError(expiryError, 'La data d\'expiració ja ha passat. Si us plau, comprova-la.');
                    ok = false;
                }

                if (!cvcValid(cvcVal)){
                    showError(cvcError, 'CVC invàlid — ha de tenir 3 o 4 dígits.');
                    ok = false;
                }

                if (!ok){
                    e.preventDefault();
                    return false;
                }

                // If valid, allow normal submit (POST to server) — server must re-validate before storing or charging.
                // Optionally: show a spinner or disable submit to avoid double submissions.
                return true;
            });
        })();
        
        // Function to delete a payment method
        function deleteCard(cardId) {
            if (!confirm('<?php echo __('profile.delete_card_confirm'); ?>')) {
                return;
            }
            
            fetch('/perfil/pagaments/delete/' + cardId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error al eliminar la targeta: ' + (data.message || 'Error desconegut'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar la targeta');
            });
        }
    </script>

</body>
</html>