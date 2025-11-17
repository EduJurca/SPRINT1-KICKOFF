<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? __('header.default_title'); ?></title>
    <link rel="icon" href="assets/images/logo.png" type="image/png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <!-- Font Awesome (icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS addicional per a cada pàgina -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- UserWay Accessibility Widget -->
    <script>
        (function(d){
            var s = d.createElement("script");
            s.setAttribute("data-account","<?php echo getenv('USERWAY_ACCOUNT_ID'); ?>");
            s.src = "https://cdn.userway.org/widget.js";
            (d.body || d.head).appendChild(s);
        })(document);
    </script>
</head>
<body class="<?php echo $bodyClass ?? 'bg-gray-100 min-h-screen'; ?>">
    <?php if (isset($showHeader) && $showHeader): ?>
        <!-- Header opcional -->
        <header class="bg-white shadow-md">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-[#1565C0]">SIMS</h1>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700"><?php echo __('header.welcome', ['username' => htmlspecialchars($_SESSION['username'] ?? '')]); ?></span>
                        <a href="/logout" class="text-red-500 hover:text-red-700"><?php echo __('header.logout'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </header>
    <?php endif; ?>
    
    <!-- Contingut principal -->
    <?php if (!isset($noMainTag) || !$noMainTag): ?>
    <main>
    <?php endif; ?>
