<!DOCTYPE html>
<html lang="ca">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo __('profile.verify_license_title'); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .phone-frame { border: 12px solid #212121; border-radius: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.5); padding: 10px; background-color: #212121; }
    .preview-img{max-width:100%; border-radius:8px;}
    .no-scrollbar::-webkit-scrollbar{display:none}
    .no-scrollbar{-ms-overflow-style:none; scrollbar-width:none}
  </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-sm md:max-w-3xl lg:max-w-4xl h-[667px] md:h-auto flex items-center justify-center">
    <div class="bg-white p-5 rounded-2xl shadow-inner w-full h-full flex flex-col relative space-y-6">
            <header class="grid grid-cols-3 items-center mb-6 w-full">
                <div class="text-left">
                    <a href="/perfil" class="text-[#1565C0] text-sm font-semibold"><?php echo __('profile.back_to_profile'); ?></a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 text-center"><?php echo __('profile.verify_license_title'); ?></h1>
                <div class="flex justify-end">
                    <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
                </div>
            </header>

      <main class="flex-1 overflow-y-auto no-scrollbar space-y-4 mt-3">
        <p class="text-gray-800"><?php echo __('profile.upload_license_photos'); ?></p>
        <ol class="list-decimal pl-5 text-gray-700 space-y-2">
          <li><?php echo __('profile.front_photo_instruction'); ?></li>
          <li><?php echo __('profile.back_photo_instruction'); ?></li>
        </ol>

        <form id="licenseForm" method="POST" action="/verificar-conduir" enctype="multipart/form-data" class="space-y-4">
          <div>
            <label class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.front_photo'); ?></label>
            <input
type="file"
name="front"
id="file_front"
accept="image/*"
required
>
            <img
id="preview_front"
alt="<?php echo __('profile.license_front_preview'); ?>"
class="mt-2 preview-img hidden"
>
          </div>
          <div>
            <label class="block text-gray-900 font-semibold mb-2"><?php echo __('profile.back_photo'); ?></label>
            <input
type="file"
name="back"
id="file_back"
accept="image/*"
required
>
            <img
id="preview_back"
alt="<?php echo __('profile.license_back_preview'); ?>"
class="mt-2 preview-img hidden"
>
          </div>
          <button type="submit" class="w-full bg-[#1565C0] text-white py-3 rounded-lg font-semibold"><?php echo __('profile.submit_for_verification'); ?></button>
        </form>
        <div id="msg" class="text-sm text-center text-gray-700"></div>
      </main>
    </div>
  </div>
  <script>
  // Solo preview de imágenes (animación/UI)
  function preview(input, imgId) {
    const file = input.files[0];
    const img = document.getElementById(imgId);
    if(file) {
      const reader = new FileReader();
      reader.onload = e => { img.src = e.target.result; img.classList.remove('hidden'); };
      reader.readAsDataURL(file);
    }
  }
  document.getElementById('file_front').addEventListener('change', e=>preview(e.target,'preview_front'));
  document.getElementById('file_back').addEventListener('change', e=>preview(e.target,'preview_back'));
  </script>
</body>
</html>