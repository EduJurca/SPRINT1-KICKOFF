<footer class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 py-3 shadow-lg z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-center space-x-8">
            <div class="flex flex-col items-center">
                <a href="/dashboard" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-home text-xl"></i>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <a href="/administrar-vehicle" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-car text-xl"></i>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <a href="/perfil" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-user text-xl"></i>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <a href="/report-incident" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
<script>
  (function() {
    var href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    var already = Array.from(document.styleSheets || []).some(function(ss){
      return ss.href && ss.href.indexOf('font-awesome') !== -1 || ss.href && ss.href.indexOf('cdnjs.cloudflare.com/ajax/libs/font-awesome') !== -1;
    }) || !!document.querySelector('link[href*="font-awesome"]') || !!document.querySelector('link[href*="cdnjs.cloudflare.com/ajax/libs/font-awesome"]');
    if (!already) {
      var l = document.createElement('link');
      l.rel = 'stylesheet';
      l.href = href;
      document.head.appendChild(l);
    }
  })();
</script>
    
    <!-- Widget de Chatbot Flotante -->
    <?php include __DIR__ . '/../commons/chatbot-widget.php'; ?>
    
    <!-- JavaScript principal -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/accessibility.js"></script>
    
    <!-- JavaScript addicional per a cada pàgina -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
