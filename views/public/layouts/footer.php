<footer class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 py-2 shadow-lg z-50">
    <div class="container mx-auto px-2">
        <div class="flex justify-around items-end">
            <div class="flex flex-col items-center">
                <a href="/dashboard" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-home text-xl mb-1"></i>
                    <span class="text-xs"><?php echo __('footer.home'); ?></span>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <a href="/administrar-vehicle" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-car text-xl mb-1"></i>
                    <span class="text-xs"><?php echo __('footer.vehicles'); ?></span>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <a href="/perfil" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-user text-xl mb-1"></i>
                    <span class="text-xs"><?php echo __('footer.profile'); ?></span>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <a href="/report-incident" class="flex flex-col items-center text-black hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-xl mb-1"></i>
                    <span class="text-xs"><?php echo __('footer.incidents'); ?></span>
                </a>
            </div>

            <div class="flex flex-col items-center">
                <button id="footer-chat-toggle" class=" border-1 flex flex-col items-center text-blue-500 hover:text-gray-500 transition-colors p-2 rounded-lg">
                    <i class="fas fa-comment-dots text-xl mb-1"></i>
                    <span class="text-xs"><?php echo __('footer.chatbot'); ?></span>
                </button>
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
    
    <?php 
    $hideChatToggle = true; // Ocultar el botón flotante cuando se usa desde footer
    include __DIR__ . '/../../commons/chatbot-widget.php'; 
    ?>
    
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const footerToggle = document.getElementById('footer-chat-toggle');
        const chatToggle = document.getElementById('chat-toggle');
        
        if (footerToggle && chatToggle) {
            footerToggle.addEventListener('click', function() {
                chatToggle.click();
            });
        }
    });
</script>
</body>
</html>
