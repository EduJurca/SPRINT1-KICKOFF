<!DOCTYPE html>
<html lang="ca">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo __('profile.complete_profile_title'); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .phone-frame { border: 12px solid #212121; border-radius: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.5); padding: 10px; background-color: #212121; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    #scrollShadow.active {
      opacity: 1;
      box-shadow: inset 0px 10px 15px -5px rgba(0,128,0,0.3);
    }
    .error-msg { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; }
  </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-sm md:max-w-3xl lg:max-w-4xl h-[667px] md:h-auto flex items-center justify-center">
    <div class="bg-white p-5 rounded-2xl shadow-inner w-full h-full flex flex-col relative space-y-6">
      <header class="grid grid-cols-3 items-center mb-6 w-full">
        <div class="text-left">
          <a href="/profile" class="text-[#1565C0] text-sm font-semibold"><?php echo __('profile.back_to_profile'); ?></a>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('profile.complete_profile_title'); ?></h1>
        <div class="flex justify-end">
          <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
        </div>
      </header>

      <div class="relative flex-1 overflow-hidden">
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
        
        <form id="completeForm" method="POST" action="/completar-perfil" class="overflow-y-auto no-scrollbar space-y-4 pr-1 h-full">
          <div>
            <label for="fullname" class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.full_name'); ?></label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname ?? ''); ?>" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
            <div class="error-msg" id="error-fullname"></div>
          </div>
          <div>
            <label for="dni" class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.dni_nie'); ?></label>
            <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($dni ?? ''); ?>" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
            <div class="error-msg" id="error-dni"></div>
          </div>
          <div>
            <label for="phone" class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.phone'); ?></label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
            <div class="error-msg" id="error-phone"></div>
          </div>
          <div>
            <label for="birthdate" class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.birth_date'); ?></label>
            <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($birthdate ?? ''); ?>" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
            <div class="error-msg" id="error-birthdate"></div>
          </div>
          <div>
            <label for="address" class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.address'); ?></label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
            <div class="error-msg" id="error-address"></div>
          </div>
          <div>
            <label for="sex" class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.gender'); ?></label>
            <select id="sex" name="sex" class="w-full px-4 py-2 bg-[#F5F5F5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1565C0]">
              <option value=""><?php echo __('profile.select_option'); ?></option>
              <option value="M" <?php echo (isset($sex) && $sex === 'M') ? 'selected' : ''; ?>><?php echo __('profile.male'); ?></option>
              <option value="F" <?php echo (isset($sex) && $sex === 'F') ? 'selected' : ''; ?>><?php echo __('profile.female'); ?></option>
              <option value="O" <?php echo (isset($sex) && $sex === 'O') ? 'selected' : ''; ?>><?php echo __('profile.other'); ?></option>
            </select>
            <div class="error-msg" id="error-sex"></div>
          </div>
          <button type="submit" class="w-full bg-[#1565C0] text-white py-3 rounded-lg font-semibold"><?php echo __('profile.save'); ?></button>
        </form>
        <div id="scrollShadow" class="pointer-events-none absolute bottom-0 left-0 w-full h-10 bg-gradient-to-t from-green-500 via-transparent to-transparent opacity-0 transition-opacity duration-300 rounded-b-2xl"></div>
      </div>
    </div>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('completeForm');
  const scrollShadow = document.getElementById('scrollShadow');

  // Scroll shadow effect (animación)
  function updateShadow() {
    if (form.scrollHeight > form.clientHeight && form.scrollTop + form.clientHeight < form.scrollHeight - 1) {
      scrollShadow.style.opacity = '1';
    } else {
      scrollShadow.style.opacity = '0';
    }
  }
  form.addEventListener('scroll', updateShadow);
  updateShadow();

  // Form validation (solo validación, no POST)
  form.addEventListener('submit', function(e) {
    // Clear previous errors
    ['fullname','dni','phone','birthdate','address','sex'].forEach(f => {
      document.getElementById('error-' + f).textContent = '';
    });

    const fullname = form.fullname.value.trim();
    const dni = form.dni.value.trim();
    const phone = form.phone.value.trim();
    const birthdate = form.birthdate.value.trim();
    const sex = form.sex.value;

    let valid = true;

    if (fullname.length < 3) {
      document.getElementById('error-fullname').textContent = '<?php echo addslashes(__('profile.name_min_length')); ?>';
      valid = false;
    }

    const dniRegex = /^(\d{8}[A-Za-z]|[XYZ]\d{7}[A-Za-z])$/;
    if (!dniRegex.test(dni)) {
      document.getElementById('error-dni').textContent = '<?php echo addslashes(__('profile.invalid_dni')); ?>';
      valid = false;
    }

    const phoneRegex = /^\d{9,}$/;
    if (!phoneRegex.test(phone)) {
      document.getElementById('error-phone').textContent = '<?php echo addslashes(__('profile.invalid_phone')); ?>';
      valid = false;
    }

    const today = new Date().toISOString().split('T')[0];
    if (!birthdate || birthdate >= today) {
      document.getElementById('error-birthdate').textContent = '<?php echo addslashes(__('profile.invalid_birthdate')); ?>';
      valid = false;
    }

    if (!['M','F','O'].includes(sex)) {
      document.getElementById('error-sex').textContent = '<?php echo addslashes(__('profile.select_gender')); ?>';
      valid = false;
    }

    if (!valid) {
      e.preventDefault();
    }
    // Si valid=true, el form se enviará normalmente al servidor
  });
});
  </script>
</body>
</html>
