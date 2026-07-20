<?php
require_once __DIR__ . '/../lib/fpdf.php';
require_once __DIR__ . '/../config/database.php';

class BudgiePdf extends FPDF
{
    protected $userName = '';
    protected $reportRange = '';

    public function setReportMeta($userName, $reportRange)
    {
        $this->userName = $userName;
        $this->reportRange = $reportRange;
    }

    function Header()
    {
        // Primary brand color background bar
        $this->SetFillColor(141, 43, 92); // #8d2b5c
        $this->Rect(0, 0, 210, 24, 'F');

        // Brand Name
        $this->SetFont('Helvetica', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(12, 6);
        $this->Cell(60, 10, $this->conv('Budgie'), 0, 0, 'L');

        // Document Title
        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(125, 10, $this->conv('RAPPORT FINANCIER'), 0, 1, 'R');

        $this->Ln(8);

        // Sub-header details
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(80, 80, 80);
        $this->Cell(100, 5, $this->conv('Client : ' . $this->userName), 0, 0, 'L');
        $this->Cell(88, 5, $this->conv('Généré le : ' . date('d/m/Y à H:i')), 0, 1, 'R');

        $this->Cell(100, 5, $this->conv('Période : ' . $this->reportRange), 0, 0, 'L');
        $this->Cell(88, 5, $this->conv('Application Budgie - Gestion Financière'), 0, 1, 'R');

        $this->Ln(4);
        $this->SetDrawColor(220, 220, 220);
        $this->Line(12, $this->GetY(), 198, $this->GetY());
        $this->Ln(6);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(100, 10, $this->conv('Budgie — Document confidentiel personnel'), 0, 0, 'L');
        $this->Cell(88, 10, $this->conv('Page ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    public function conv($str)
    {
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
            if ($converted !== false) return $converted;
        }
        return utf8_decode($str);
    }
}

class PdfReportService
{
    public static function generateReport($userId, $range = '1_month')
    {
        // 1. Fetch user info
        $user = fetchOne("SELECT first_name, last_name, email FROM users WHERE id = ?", [(int)$userId]);
        $userName = $user ? ($user['first_name'] . ' ' . $user['last_name']) : 'Utilisateur';

        // 2. Determine date filter
        $whereIncSql = "WHERE i.user_id = ? AND i.deleted_at IS NULL";
        $whereExpSql = "WHERE e.user_id = ? AND e.deleted_at IS NULL";
        $params = [(int)$userId];
        $rangeLabel = 'Toutes les données';

        if ($range === '1_month') {
            $whereIncSql .= " AND i.start_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            $whereExpSql .= " AND e.start_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            $rangeLabel = 'Dernier mois (30 derniers jours)';
        } elseif ($range === '3_months') {
            $whereIncSql .= " AND i.start_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            $whereExpSql .= " AND e.start_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            $rangeLabel = '3 derniers mois (90 derniers jours)';
        }

        // 3. Fetch incomes
        $incomes = fetchAll("
            SELECT i.*, a.name as account_name 
            FROM incomes i
            LEFT JOIN accounts a ON i.account_id = a.id
            $whereIncSql
            ORDER BY i.start_date DESC
        ", $params);

        // 4. Fetch expenses
        $expenses = fetchAll("
            SELECT e.*, a.name as account_name 
            FROM expenses e
            LEFT JOIN accounts a ON e.account_id = a.id
            $whereExpSql
            ORDER BY e.start_date DESC
        ", $params);

        // Calculate totals
        $totalIncome = 0.00;
        foreach ($incomes as $inc) {
            $totalIncome += (float)$inc['amount'];
        }

        $totalExpense = 0.00;
        foreach ($expenses as $exp) {
            $totalExpense += (float)$exp['amount'];
        }

        $netBalance = $totalIncome - $totalExpense;

        // 5. Initialize PDF
        $pdf = new BudgiePdf('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->setReportMeta($userName, $rangeLabel);
        $pdf->SetTitle('Budgie Rapport Financier - ' . $rangeLabel);
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();

        // --- Summary Box ---
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 7, $pdf->conv('Synthèse Financière'), 0, 1, 'L');

        // Draw 3 Summary Cards
        $cardW = 58;
        $cardH = 18;
        $startY = $pdf->GetY();

        // Income Card (Green)
        $pdf->SetFillColor(240, 253, 244);
        $pdf->SetDrawColor(187, 247, 208);
        $pdf->Rect(12, $startY, $cardW, $cardH, 'DF');
        $pdf->SetXY(14, $startY + 3);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(22, 101, 52);
        $pdf->Cell($cardW - 4, 4, $pdf->conv('TOTAL REVENUS'), 0, 1, 'L');
        $pdf->SetX(14);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell($cardW - 4, 7, $pdf->conv('+ ' . number_format($totalIncome, 2, ',', ' ') . ' €'), 0, 1, 'L');

        // Expense Card (Red)
        $pdf->SetFillColor(254, 242, 242);
        $pdf->SetDrawColor(254, 202, 202);
        $pdf->Rect(76, $startY, $cardW, $cardH, 'DF');
        $pdf->SetXY(78, $startY + 3);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(153, 27, 27);
        $pdf->Cell($cardW - 4, 4, $pdf->conv('TOTAL DÉPENSES'), 0, 1, 'L');
        $pdf->SetX(78);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell($cardW - 4, 7, $pdf->conv('- ' . number_format($totalExpense, 2, ',', ' ') . ' €'), 0, 1, 'L');

        // Net Balance Card (Wine / Primary)
        $pdf->SetFillColor(250, 245, 248);
        $pdf->SetDrawColor(242, 210, 227);
        $pdf->Rect(140, $startY, $cardW, $cardH, 'DF');
        $pdf->SetXY(142, $startY + 3);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(141, 43, 92);
        $pdf->Cell($cardW - 4, 4, $pdf->conv('SOLDE NET'), 0, 1, 'L');
        $pdf->SetX(142);
        $pdf->SetFont('Helvetica', 'B', 12);
        $sign = $netBalance >= 0 ? '+ ' : '';
        $pdf->Cell($cardW - 4, 7, $pdf->conv($sign . number_format($netBalance, 2, ',', ' ') . ' €'), 0, 1, 'L');

        $pdf->SetY($startY + $cardH + 8);

        // --- Incomes Table ---
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(16, 185, 129); // Green accent
        $pdf->Cell(0, 7, $pdf->conv('Detail des Revenus (' . count($incomes) . ')'), 0, 1, 'L');

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetFillColor(235, 248, 242);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetDrawColor(200, 230, 215);

        // Header columns: Date(28), Compte(42), Description(60), Fréquence(26), Montant(30) = 186mm
        $pdf->Cell(28, 7, $pdf->conv('Date Début'), 1, 0, 'L', true);
        $pdf->Cell(42, 7, $pdf->conv('Compte'), 1, 0, 'L', true);
        $pdf->Cell(60, 7, $pdf->conv('Description / Nom'), 1, 0, 'L', true);
        $pdf->Cell(26, 7, $pdf->conv('Fréquence'), 1, 0, 'C', true);
        $pdf->Cell(30, 7, $pdf->conv('Montant (€)'), 1, 1, 'R', true);

        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetTextColor(50, 50, 50);

        if (empty($incomes)) {
            $pdf->Cell(186, 7, $pdf->conv('Aucun revenu enregistré sur cette période.'), 1, 1, 'C');
        } else {
            $fill = false;
            foreach ($incomes as $inc) {
                $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 249 : 255);
                $pdf->Cell(28, 6.5, date('d/m/Y', strtotime($inc['start_date'])), 'LRB', 0, 'L', true);
                $pdf->Cell(42, 6.5, $pdf->conv(substr($inc['account_name'] ?? 'Non assigné', 0, 24)), 'LRB', 0, 'L', true);
                $pdf->Cell(60, 6.5, $pdf->conv(substr($inc['name'], 0, 36)), 'LRB', 0, 'L', true);
                $pdf->Cell(26, 6.5, $pdf->conv(ucfirst($inc['frequency'])), 'LRB', 0, 'C', true);
                $pdf->Cell(30, 6.5, number_format($inc['amount'], 2, ',', ' ') . ' €', 'LRB', 1, 'R', true);
                $fill = !$fill;
            }
        }

        $pdf->Ln(8);

        // --- Expenses Table ---
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(239, 68, 68); // Red accent
        $pdf->Cell(0, 7, $pdf->conv('Detail des Dépenses (' . count($expenses) . ')'), 0, 1, 'L');

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetFillColor(254, 242, 242);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetDrawColor(245, 215, 215);

        $pdf->Cell(28, 7, $pdf->conv('Date Début'), 1, 0, 'L', true);
        $pdf->Cell(42, 7, $pdf->conv('Compte'), 1, 0, 'L', true);
        $pdf->Cell(60, 7, $pdf->conv('Description / Nom'), 1, 0, 'L', true);
        $pdf->Cell(26, 7, $pdf->conv('Fréquence'), 1, 0, 'C', true);
        $pdf->Cell(30, 7, $pdf->conv('Montant (€)'), 1, 1, 'R', true);

        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetTextColor(50, 50, 50);

        if (empty($expenses)) {
            $pdf->Cell(186, 7, $pdf->conv('Aucune dépense enregistrée sur cette période.'), 1, 1, 'C');
        } else {
            $fill = false;
            foreach ($expenses as $exp) {
                $pdf->SetFillColor($fill ? 253 : 255, $fill ? 248 : 255, $fill ? 248 : 255);
                $pdf->Cell(28, 6.5, date('d/m/Y', strtotime($exp['start_date'])), 'LRB', 0, 'L', true);
                $pdf->Cell(42, 6.5, $pdf->conv(substr($exp['account_name'] ?? 'Non assigné', 0, 24)), 'LRB', 0, 'L', true);
                $pdf->Cell(60, 6.5, $pdf->conv(substr($exp['name'], 0, 36)), 'LRB', 0, 'L', true);
                $pdf->Cell(26, 6.5, $pdf->conv(ucfirst($exp['frequency'])), 'LRB', 0, 'C', true);
                $pdf->Cell(30, 6.5, number_format($exp['amount'], 2, ',', ' ') . ' €', 'LRB', 1, 'R', true);
                $fill = !$fill;
            }
        }

        // Return PDF as string output
        return $pdf->Output('S');
    }
}
