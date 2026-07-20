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

    <!-- PDF Export Modal -->
    <div id="exportPdfModal" class="modal">
        <div class="modal-content" style="max-width: 460px; padding: 2rem;">
            <div class="modal-header">
                <h3 class="modal-title" style="display:flex;align-items:center;gap:.5rem">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Exporter le Rapport PDF
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('exportPdfModal')">&times;</button>
            </div>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">
                Choisissez la période pour le bilan complet de vos dépenses et revenus :
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.5rem;">
                <a href="actions/export_pdf.php?range=1_month" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem; padding: 0.9rem 1.2rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div style="text-align: left;">
                        <div>Dernier mois</div>
                        <small style="font-weight: normal; opacity: 0.75; font-size: 0.78rem;">30 derniers jours calendaires</small>
                    </div>
                </a>

                <a href="actions/export_pdf.php?range=3_months" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem; padding: 0.9rem 1.2rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <div style="text-align: left;">
                        <div>3 derniers mois</div>
                        <small style="font-weight: normal; opacity: 0.75; font-size: 0.78rem;">90 derniers jours (Trimestre)</small>
                    </div>
                </a>

                <a href="actions/export_pdf.php?range=all" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem; padding: 0.9rem 1.2rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <div style="text-align: left;">
                        <div>Toutes les données</div>
                        <small style="font-weight: normal; opacity: 0.75; font-size: 0.78rem;">Historique financier complet</small>
                    </div>
                </a>
            </div>

            <div style="text-align: right;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('exportPdfModal')">Fermer</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/main.js"></script>
    <script src="js/charts.js"></script>
</body>
</html>
