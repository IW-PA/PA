<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/services/PdfReportService.php';

class PdfReportServiceTest extends TestCase
{
    public function testPdfGenerationForDifferentRanges()
    {
        // Fetch a valid user ID or default to 1
        $user = fetchOne("SELECT id FROM users LIMIT 1");
        $userId = $user ? (int)$user['id'] : 1;

        // Test 1 Month PDF
        $pdf1Month = PdfReportService::generateReport($userId, '1_month');
        $this->assertEquals(true, !empty($pdf1Month), '1_month PDF should not be empty');
        $this->assertEquals('%PDF-', substr($pdf1Month, 0, 5), '1_month output should start with PDF header');

        // Test 3 Months PDF
        $pdf3Months = PdfReportService::generateReport($userId, '3_months');
        $this->assertEquals(true, !empty($pdf3Months), '3_months PDF should not be empty');
        $this->assertEquals('%PDF-', substr($pdf3Months, 0, 5), '3_months output should start with PDF header');

        // Test All Time PDF
        $pdfAll = PdfReportService::generateReport($userId, 'all');
        $this->assertEquals(true, !empty($pdfAll), 'All time PDF should not be empty');
        $this->assertEquals('%PDF-', substr($pdfAll, 0, 5), 'All time output should start with PDF header');
    }
}
