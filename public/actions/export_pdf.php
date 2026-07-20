<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/PdfReportService.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

$userId = $_SESSION['user_id'];
$range  = sanitizeInput($_GET['range'] ?? '1_month');

if (!in_array($range, ['1_month', '3_months', 'all'])) {
    $range = '1_month';
}

try {
    $pdfBuffer = PdfReportService::generateReport($userId, $range);

    // Log activity
    ActivityLogger::log($userId, 'export.pdf_downloaded', 'report', null, ['range' => $range]);

    // Clean output buffer if any text was emitted
    if (ob_get_level()) {
        ob_end_clean();
    }

    $filename = 'Budgie_Rapport_' . $range . '_' . date('Y-m-d') . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($pdfBuffer));

    echo $pdfBuffer;
    exit;

} catch (Exception $e) {
    error_log("PDF Generation error: " . $e->getMessage());
    setFlashMessage('error', 'Une erreur est survenue lors de la génération du rapport PDF.');
    redirect('index.php');
}
