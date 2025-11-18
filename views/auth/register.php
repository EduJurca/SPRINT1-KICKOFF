<!DOCTYPE html>
<html lang="ca">
<?php require_once __DIR__ . '/../../locale/Lang.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/assets/images/favicon.png">
    <title>SIMS - Registrar-se</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-none {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div
        class="bg-white p-6 rounded-2xl shadow-inner w-full max-w-sm flex flex-col justify-start overflow-y-auto box-border scrollbar-none">
        <h1 class="text-2xl font-bold text-center text-gray-900 mb-6">Registrar-se</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/register" id="registerForm" novalidate>
            <div class="mb-4">
                <label for="username" class="block text-gray-900 font-semibold mb-2">Nom d'usuari *</label>
                <input type="text" id="username" name="username" required minlength="3"
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="Nom d'usuari">
                <p id="error-username" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-900 font-semibold mb-2">Correu *</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="El teu correu electrònic">
                <p id="error-email" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-900 font-semibold mb-2">Contrasenya *</label>
                <input type="password" id="password" name="password" required minlength="8"
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="••••••••">
                <p id="error-password" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <button type="submit"
                class="w-full bg-[#1565C0] text-white font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300">
                Registrar
            </button>
        </form>

        <p class="text-center mt-4">
            Ja tens compte? <a href="/login" class="text-[#1565C0] hover:underline">Inicia sessió</a>
        </p>
    </div>
    
    <script>
        // Validació personalitzada amb missatges traduïts
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const errorUsername = document.getElementById('error-username');
            const errorEmail = document.getElementById('error-email');
            const errorPassword = document.getElementById('error-password');
            
            // Missatges traduïts
            const requiredMsg = '<?php echo addslashes(__('form.validations.required_field')); ?>';

            // Validar formulari al submit
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Validar username
                if (username.value.trim() === '') {
                    errorUsername.textContent = requiredMsg;
                    errorUsername.classList.remove('hidden');
                    username.classList.add('border-red-500');
                    isValid = false;
                } else if (username.value.trim().length < 3) {
                    errorUsername.textContent = 'Mínim 3 caràcters';
                    errorUsername.classList.remove('hidden');
                    username.classList.add('border-red-500');
                    isValid = false;
                } else {
                    errorUsername.classList.add('hidden');
                    username.classList.remove('border-red-500');
                }

                // Validar email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email.value.trim() === '') {
                    errorEmail.textContent = requiredMsg;
                    errorEmail.classList.remove('hidden');
                    email.classList.add('border-red-500');
                    isValid = false;
                } else if (!emailRegex.test(email.value.trim())) {
                    errorEmail.textContent = 'Correu no vàlid';
                    errorEmail.classList.remove('hidden');
                    email.classList.add('border-red-500');
                    isValid = false;
                } else {
                    errorEmail.classList.add('hidden');
                    email.classList.remove('border-red-500');
                }

                // Validar password
                if (password.value === '') {
                    errorPassword.textContent = requiredMsg;
                    errorPassword.classList.remove('hidden');
                    password.classList.add('border-red-500');
                    isValid = false;
                } else if (password.value.length < 8) {
                    errorPassword.textContent = 'Mínim 8 caràcters';
                    errorPassword.classList.remove('hidden');
                    password.classList.add('border-red-500');
                    isValid = false;
                } else {
                    errorPassword.classList.add('hidden');
                    password.classList.remove('border-red-500');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Netejar errors quan l'usuari escriu
            username.addEventListener('input', function() {
                errorUsername.classList.add('hidden');
                username.classList.remove('border-red-500');
            });

            email.addEventListener('input', function() {
                errorEmail.classList.add('hidden');
                email.classList.remove('border-red-500');
            });

            password.addEventListener('input', function() {
                errorPassword.classList.add('hidden');
                password.classList.remove('border-red-500');
            });
        });
    </script>
</body>

</html>