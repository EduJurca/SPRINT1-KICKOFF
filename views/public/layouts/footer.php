    <?php if (!isset($noMainTag) || !$noMainTag): ?>
    </main>
    <?php endif; ?>
    
    <?php if (isset($showFooter) && $showFooter): ?>
        <!-- Footer opcional -->
        <footer class="bg-gray-800 text-white py-4 mt-8">
            <div class="container mx-auto px-4 text-center">
                <p><?php echo __('footer.copyright', ['year' => date('Y')]); ?></p>
            </div>
        </footer>
    <?php endif; ?>
    
    <!-- Widget de Chatbot Flotante -->
    <?php include __DIR__ . '/../commons/chatbot-widget.php'; ?>
    
    <!-- JavaScript principal -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/auth.js"></script>
    
    <!-- JavaScript addicional per a cada pàgina -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
