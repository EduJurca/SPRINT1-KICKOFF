            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/accessibility.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ((array)$additionalJS as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
