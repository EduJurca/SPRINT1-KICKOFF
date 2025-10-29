<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        <form method="POST" action="/register" id="registerForm">
            <div class="mb-4">
                <label for="username" class="block text-gray-900 font-semibold mb-2">Nom d'usuari *</label>
                <input type="text" id="username" name="username" required minlength="3"
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="Nom d'usuari">
                <span id="error-username" class="text-red-600 text-sm"></span>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-900 font-semibold mb-2">Correu *</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="El teu correu electrònic">
                <span id="error-email" class="text-red-600 text-sm"></span>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-900 font-semibold mb-2">Contrasenya *</label>
                <input type="password" id="password" name="password" required minlength="8"
                    class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]"
                    placeholder="••••••••">
                <span id="error-password" class="text-red-600 text-sm"></span>
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
        // Solo validación client-side
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            let valid = true;
            
            // Limpiar errores
            document.querySelectorAll('span[id^="error-"]').forEach(el => el.textContent = '');
            
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (username.length < 3) {
                document.getElementById('error-username').textContent = 'Mínim 3 caràcters';
                valid = false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('error-email').textContent = 'Correu no vàlid';
                valid = false;
            }
            
            if (password.length < 8) {
                document.getElementById('error-password').textContent = 'Mínim 8 caràcters';
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>
