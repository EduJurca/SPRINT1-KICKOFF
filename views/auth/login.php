<!DOCTYPE html>
<html lang="ca">
<?php require_once __DIR__ . '/../../locale/Lang.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/assets/images/favicon.png">
    <title>SIMS - Inicia Sessió</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/toast.js"></script>
    <style>
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-inner w-full max-w-sm flex flex-col justify-center">
        <img src="/assets/images/logo.png" alt="Logotip de SIMS" class="h-40 w-40 rounded-full mx-auto">
        <h1 class="text-2xl font-bold text-center text-gray-900 mb-6">Inicia Sessió</h1>
        
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
        
        <form method="POST" action="/login" autocomplete="off" novalidate>
            <div class="mb-4">
                <label for="username" class="block text-gray-900 font-semibold mb-2">Usuari *</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="Nom d'usuari">
                <p id="error-username" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-900 font-semibold mb-2">Contrasenya *</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="••••••••">
                <p id="error-password" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <p class="text-right mt-2">
                <a href="/recover-password" class="text-[#1565C0] hover:underline text-sm">He oblidat la
                    contrasenya</a>
            </p>
            <button type="submit"
                class="w-full bg-[#1565C0] text-white font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300">
                Iniciar
            </button>
        </form>
        <p class="text-center mt-4">
            No tens compte? <a href="/register" class="text-[#1565C0] hover:underline">Registra't</a>
        </p>
    </div>

    <script>
        // Validació personalitzada amb missatges sota els camps
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            const errorUsername = document.getElementById('error-username');
            const errorPassword = document.getElementById('error-password');
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
                } else {
                    errorUsername.classList.add('hidden');
                    username.classList.remove('border-red-500');
                }

                // Validar password
                if (password.value.trim() === '') {
                    errorPassword.textContent = requiredMsg;
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

            password.addEventListener('input', function() {
                errorPassword.classList.add('hidden');
                password.classList.remove('border-red-500');
            });
        });
    </script>
</body>

</html>