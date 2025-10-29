            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/public_html/js/main.js"></script>
    <script src="/public_html/js/accessibility.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ((array)$additionalJS as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
