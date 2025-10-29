    <?php if (!isset($noMainTag) || !$noMainTag): ?>
    </main>
    <?php endif; ?>
    
    <?php if (isset($showFooter) && $showFooter): ?>
        <!-- Footer opcional -->
        <footer class="bg-gray-800 text-white py-4 mt-8">
            <div class="container mx-auto px-4 text-center">
                <p>&copy; <?php echo date('Y'); ?> SIMS - Tots els drets reservats</p>
            </div>
        </footer>
    <?php endif; ?>
    
    <!-- JavaScript principal -->
    <script src="/public_html/js/main.js"></script>
    <script src="/public_html/js/auth.js"></script>
    <script src="/public_html/js/accessibility.js"></script>
    
    <!-- JavaScript addicional per a cada pàgina -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
