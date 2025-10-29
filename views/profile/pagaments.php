<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMS - Pagaments</title>
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
                    <a href="/perfil" class="text-[#1565C0] text-sm font-semibold">← Tornar</a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center">Pagaments</h1>
                <div class="flex justify-end">
                    <img src="/public_html/images/logo.png" alt="Logo" class="h-10 w-10">
                </div>
            </header>
            
            <div class="mb-6 border-b pb-4">
                <h2 class="text-xl font-semibold mb-4 text-gray-900">Targetes Actuals</h2>
                <ul class="space-y-2">
                    <li class="bg-[#F5F5F5] p-3 rounded-lg shadow-sm">
                        <p class="text-gray-700">VISA **** **** **** 1234</p>
                    </li>
                </ul>
            </div>
            
            <a href="#" class="block w-full bg-[#1565C0] text-white font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity duration-300 text-center">
                Afegir Targeta
            </a>
        </div>
    </div>
</body>
</html>