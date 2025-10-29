<!DOCTYPE html>
<html lang="ca">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMS - Perfil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="/assets/js/toast.js"></script>
  <style>
    .pushable {
      position: relative;
      border: none;
      border-radius: 12px;
      padding: 1rem 2rem;
      color: white;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      outline-offset: 4px;
      background: linear-gradient(180deg, #1565C0 0%, #0D47A1 100%);
      box-shadow: 0 6px #0D47A1;
      user-select: none;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .pushable:active {
      box-shadow: 0 3px #0D47A1;
      transform: translateY(3px);
    }

    .action-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 2rem 1rem;
      border-radius: 1rem;
      background-color: #E5E7EB;
      color: #1F2937;
      font-weight: 600;
      text-align: center;
      transition: background 0.3s;
    }

    .action-card:hover {
      background-color: #D1D5DB;
    }

    .action-card svg {
      width: 32px;
      height: 32px;
      fill: currentColor;
    }
  </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

  <div class="w-full max-w-4xl bg-white p-6 rounded-2xl shadow-lg">

    <header class="w-full mb-6 grid grid-cols-3 items-center">
      <div class="text-left">
        <a href="/dashboard" class="text-[#1565C0] font-semibold hover:underline">← Tornar</a>
      </div>
      <h1 class="text-center text-2xl font-bold text-gray-900">Perfil</h1>
      <div class="flex justify-end">
        <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
      </div>
    </header>

    <div class="flex flex-col md:flex-row gap-8">
    <!-- Left column: Personal data -->

      <div class="flex-shrink-0 w-full md:w-1/3 p-4">
        <h2 class="text-2xl font-bold mb-4 text-gray-900">Dades Personals</h2>
        <div class="space-y-3">
          <p><strong>Nom:</strong> <span id="fullname_span"><?php echo htmlspecialchars($fullname ?? $username ?? 'No definit'); ?></span></p>
          <p><strong>DNI:</strong> <span id="dni_span"><?php echo htmlspecialchars($dni ?? 'No definit'); ?></span></p>
          <p><strong>Telèfon:</strong> <span id="phone_span"><?php echo htmlspecialchars($phone ?? 'No definit'); ?></span></p>
          <p><strong>Data naixement:</strong> <span id="birthdate_span"><?php echo htmlspecialchars($birthdate ?? 'No definit'); ?></span></p>
          <p><strong>Adreça:</strong> <span id="address_span"><?php echo htmlspecialchars($address ?? 'No definit'); ?></span></p>
          <p><strong>Sexe:</strong> <span id="sex_span"><?php 
            if (isset($sex)) {
              echo $sex === 'M' ? 'Home' : ($sex === 'F' ? 'Dona' : ($sex === 'O' ? 'Altres' : 'No definit'));
            } else {
              echo 'No definit';
            }
          ?></span></p>
        </div>
        <div class="mt-4 flex gap-2">
          <button id="editBtn" class="pushable flex-1">Editar</button>
          <button id="saveBtn" class="pushable flex-1 bg-green-600 hidden">Guardar</button>
        </div>
      </div>

    <!-- Right column: Actions -->

      <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-6">

        <a href="/completar-perfil" class="action-card">
          <svg viewBox="0 0 24 24">
            <path
              d="M12 12c2.7 0 4.9-2.2 4.9-4.9S14.7 2.2 12 2.2 7.1 4.4 7.1 7.1 9.3 12 12 12zm0 2.2c-3 0-9 1.5-9 4.5v2.2h18v-2.2c0-3-6-4.5-9-4.5z" />
          </svg>
          Completar Perfil
        </a>

        <a href="/verificar-conduir" class="action-card">
          <svg viewBox="0 0 24 24">
            <path
              d="M20 2H4C2.9 2 2 2.9 2 4v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-9 16c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z" />
          </svg>
          Verificar Carnet
        </a>

        <a href="/historial" class="action-card">
          <svg viewBox="0 0 24 24">
            <path d="M13 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9H13V3z" />
          </svg>
          Històric Viatges
        </a>

        <a href="/pagaments" class="action-card">
          <svg viewBox="0 0 24 24">
            <path
              d="M21 4H3c-1.1 0-2 .9-2 2v2h22V6c0-1.1-.9-2-2-2zm0 4H1v10c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V8zm-2 3c0 .6-.4 1-1 1s-1-.4-1-1 .4-1 1-1 1 .4 1 1z" />
          </svg>
          Pagaments
        </a>

      </div>
    </div>
  </div>

  </div>

  <script>
    // Solo animación de UI - La edición se hace por formulario HTML
    document.addEventListener('DOMContentLoaded', function () {
      const editBtn = document.getElementById('editBtn');
      const saveBtn = document.getElementById('saveBtn');
      
      // Ocultar funcionalidad de edición inline (debe hacerse por formulario separado)
      if (editBtn) editBtn.style.display = 'none';
      if (saveBtn) saveBtn.style.display = 'none';
    });
  </script>



</body>

</html>