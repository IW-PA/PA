<?php
// Footer component for Budgie
?>
    <?php if (!in_array(basename($_SERVER['PHP_SELF'], '.php'), ['login', 'signup'])): ?>
        </main>
    </div>
    <?php endif; ?>

    <!-- Mobile Overlay -->
    <div id="mobileOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;"></div>

    <!-- Scripts -->
    <script src="js/main.js"></script>
    <script src="js/charts.js"></script>
</body>
</html>
