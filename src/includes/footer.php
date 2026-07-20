<?php
// Footer component for Budgie
?>
    <?php if (!in_array(basename($_SERVER['PHP_SELF'], '.php'), ['login', 'signup'])): ?>
        </main>
    </div>
    <?php endif; ?>

    <div id="mobileOverlay" class="sidebar-overlay"></div>

    <!-- Custom Confirmation Modal -->
    <div id="customConfirmModal" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center; padding: 2rem;">
            <h3 id="confirmModalTitle" class="modal-title" style="margin-bottom: 0.75rem; justify-content: center;">Confirmation</h3>
            <p id="confirmModalMessage" class="text-muted" style="margin-bottom: 1.5rem; line-height: 1.5; font-size: 0.95rem;"></p>
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <button type="button" id="confirmModalCancelBtn" class="btn btn-secondary" style="min-width: 100px;">Annuler</button>
                <button type="button" id="confirmModalOkBtn" class="btn btn-danger" style="min-width: 100px;">Confirmer</button>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal -->
    <div id="customAlertModal" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center; padding: 2rem;">
            <h3 id="alertModalTitle" class="modal-title" style="margin-bottom: 0.75rem; justify-content: center;">Information</h3>
            <p id="alertModalMessage" class="text-muted" style="margin-bottom: 1.5rem; line-height: 1.5; font-size: 0.95rem;"></p>
            <div style="display: flex; justify-content: center;">
                <button type="button" id="alertModalOkBtn" class="btn btn-primary" style="min-width: 120px;">D'accord</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/main.js"></script>
    <script src="js/charts.js"></script>
</body>
</html>
