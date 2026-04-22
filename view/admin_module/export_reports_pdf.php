<?php
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once MODEL_PATH . 'Report.php';
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";

// ── FILTER PARAMS ─────────────────────────────────────────────
$filterStatus   = $_GET['status']   ?? '';
$filterCategory = $_GET['category'] ?? '';
$filterSearch   = trim($_GET['search'] ?? '');

class ReportsPDF extends FPDF
{
    // Landscape A4: 297 × 210 mm
    public float $PW = 297;
    public float $ML = 10;
    public float $MR = 10;

    public function usableWidth(): float
    {
        return $this->PW - $this->ML - $this->MR; // 277 mm
    }

    // ── HEADER ────────────────────────────────────────────────
    public function Header()
    {
        $pw = $this->PW;

        // Full-width orange bar
        $this->SetFillColor(255, 107, 53);
        $this->Rect(0, 0, $pw, 20, 'F');

        // Brand
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(12, 5);
        $this->Cell(120, 10, 'AGAP-Link', 0, 0, 'L');

        // Page number
        $this->SetFont('Arial', '', 8);
        $this->SetXY(0, 7);
        $this->Cell($pw - 10, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');

        // Title
        $this->SetTextColor(26, 35, 50);
        $this->SetFont('Arial', 'B', 15);
        $this->SetXY(12, 26);
        $this->Cell(0, 8, 'Reports Management Export', 0, 1, 'L');

        // Date
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);
        $this->SetX(12);
        $this->Cell(0, 5, 'Generated: ' . date('F d, Y  h:i A'), 0, 1, 'L');

        // Full-width divider
        $this->SetDrawColor(255, 107, 53);
        $this->SetLineWidth(0.6);
        $this->Line($this->ML, $this->GetY() + 2, $pw - $this->MR, $this->GetY() + 2);
        $this->SetLineWidth(0.2);
        $this->Ln(6);
    }

    // ── FOOTER ────────────────────────────────────────────────
    public function Footer()
    {
        $pw   = $this->PW;
        $half = $this->usableWidth() / 2;
        $this->SetY(-13);
        $this->SetDrawColor(203, 213, 225);
        $this->Line($this->ML, $this->GetY(), $pw - $this->MR, $this->GetY());
        $this->Ln(1);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(148, 163, 184);
        $this->SetX($this->ML);
        $this->Cell($half, 6, 'AGAP-Link Reporting System', 0, 0, 'L');
        $this->Cell($half, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }

    // ── TRUNCATE ──────────────────────────────────────────────
    public function truncate(string $text, float $maxW, string $font = 'Arial', string $style = '', int $size = 8): string
    {
        $this->SetFont($font, $style, $size);
        if ($this->GetStringWidth($text) <= $maxW) return $text;
        while (mb_strlen($text) > 0 && $this->GetStringWidth($text . '…') > $maxW) {
            $text = mb_substr($text, 0, -1);
        }
        return $text . '…';
    }

    // ── TABLE HEADER ─────────────────────────────────────────
    public function TableHeader(array $cols, float $h): void
    {
        $this->SetFillColor(44, 62, 80);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->SetDrawColor(44, 62, 80);
        $this->SetX($this->ML);
        foreach ($cols as $label => $w) {
            $this->Cell($w, $h, $label, 1, 0, 'C', true);
        }
        $this->Ln();
    }

    // ── STATUS COLOR ─────────────────────────────────────────
    public function statusColor(string $s): array
    {
        return match (strtolower(trim($s))) {
            'resolved'  => [5,   150, 105],
            'pending'   => [180,  83,   9],
            'verified'  => [29,   78, 216],
            'forwarded' => [109,  40, 217],
            'ongoing'   => [220, 108,   0],
            default     => [30,   41,  59],
        };
    }

    // ── SAFE IMAGE ────────────────────────────────────────────
    public function safeImage(string $path, float $x, float $y, float $w, float $h): void
    {
        if (!file_exists($path) || !is_readable($path)) return;
        $info = @getimagesize($path);
        if (!$info) return;
        $ext = strtolower(image_type_to_extension($info[2], false));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return;
        try { $this->Image($path, $x, $y, $w, $h); } catch (Throwable $e) {}
    }
}

// ── DATA ─────────────────────────────────────────────────────
$reportModel = new Report();
$reports = method_exists($reportModel, 'getFilteredReports')
    ? $reportModel->getFilteredReports($filterStatus, $filterCategory, $filterSearch)
    : $reportModel->getAllReports();

// ── INIT PDF ─────────────────────────────────────────────────
$pdf = new ReportsPDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 48, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// ── COLUMN WIDTHS  (must total 277 mm = usable width) ────────
// ID(14) + Cat(40) + Reporter(40) + Status(28) + Agency(58) + Date(30) + Photo(67) = 277
$cols = [
    'ID'       =>  14,
    'Category' =>  40,
    'Reporter' =>  40,
    'Status'   =>  28,
    'Agency'   =>  58,
    'Date'     =>  30,
    'Photo'    =>  67,
];

$ROW_H = 24;   // row height (mm)
$IMG_W = 55;   // thumbnail width  inside photo cell
$IMG_H = 19;   // thumbnail height

// ── SUMMARY BOX ──────────────────────────────────────────────
$uw       = $pdf->usableWidth();   // 277 mm
$summaryY = $pdf->GetY();

$pdf->SetFillColor(255, 237, 230);
$pdf->SetDrawColor(255, 107, 53);
$pdf->SetLineWidth(0.4);
$pdf->Rect(10, $summaryY, $uw, 13, 'DF');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(29, 78, 216);
$pdf->SetXY(14, $summaryY + 3);
$pdf->Cell(80, 7, 'Total Reports: ' . count($reports), 0, 0, 'L');

$filterParts = [];
if ($filterStatus)   $filterParts[] = 'Status: ' . $filterStatus;
if ($filterCategory) $filterParts[] = 'Category filter applied';
if ($filterSearch)   $filterParts[] = 'Search: "' . $filterSearch . '"';
if ($filterParts) {
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->SetXY(110, $summaryY + 4);
    $pdf->Cell(0, 5, 'Filters: ' . implode('  |  ', $filterParts), 0, 0, 'L');
}

$pdf->Ln(17);

// ── TABLE ────────────────────────────────────────────────────
$headerH = 9;
$pdf->TableHeader($cols, $headerH);

$rowNum = 0;
foreach ($reports as $r) {
    $rowNum++;

    if ($pdf->GetY() + $ROW_H > ($pdf->GetPageHeight() - 18)) {
        $pdf->AddPage();
        $pdf->TableHeader($cols, $headerH);
    }

    [$bgR, $bgG, $bgB] = ($rowNum % 2 === 0) ? [255, 255, 255] : [248, 250, 252];
    $pdf->SetFillColor($bgR, $bgG, $bgB);
    $pdf->SetDrawColor(203, 213, 225);
    $pdf->SetLineWidth(0.2);

    $startX = 10;
    $startY = $pdf->GetY();
    $pdf->SetXY($startX, $startY);

    // ID
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 41, 59);
    $pdf->Cell($cols['ID'], $ROW_H, '#' . $r['report_id'], 1, 0, 'C', true);

    // Category
    $cat = $pdf->truncate($r['category_name'] ?? 'General', $cols['Category'] - 4);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 41, 59);
    $pdf->Cell($cols['Category'], $ROW_H, $cat, 1, 0, 'C', true);

    // Reporter
    $name = $pdf->truncate($r['full_name'] ?? 'N/A', $cols['Reporter'] - 4);
    $pdf->Cell($cols['Reporter'], $ROW_H, $name, 1, 0, 'C', true);

    // Status (colored)
    [$cr, $cg, $cb] = $pdf->statusColor($r['status']);
    $pdf->SetTextColor($cr, $cg, $cb);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($cols['Status'], $ROW_H, $r['status'], 1, 0, 'C', true);
    $pdf->SetTextColor(30, 41, 59);
    $pdf->SetFont('Arial', '', 8);

    // Agency
    $agency = $pdf->truncate($r['agency_name'] ?? '—', $cols['Agency'] - 4);
    $pdf->Cell($cols['Agency'], $ROW_H, $agency, 1, 0, 'C', true);

    // Date
    $pdf->Cell($cols['Date'], $ROW_H, date('M d, Y', strtotime($r['created_at'])), 1, 0, 'C', true);

    // Photo cell — draw border first, then overlay image
    $photoX = $pdf->GetX();
    $pdf->Cell($cols['Photo'], $ROW_H, '', 1, 0, 'C', true);

    $imgPath = !empty($r['photo_path'])
        ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($r['photo_path'], '/')
        : null;

    if ($imgPath && file_exists($imgPath)) {
        $imgX = $photoX + ($cols['Photo'] - $IMG_W) / 2;
        $imgY = $startY  + ($ROW_H       - $IMG_H) / 2;
        $pdf->safeImage($imgPath, $imgX, $imgY, $IMG_W, $IMG_H);
    } else {
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->SetTextColor(156, 163, 175);
        $pdf->SetXY($photoX, $startY + $ROW_H / 2 - 2);
        $pdf->Cell($cols['Photo'], 4, 'No photo', 0, 0, 'C');
        $pdf->SetTextColor(30, 41, 59);
    }

    // Advance to next row using absolute Y
    $pdf->SetXY($startX, $startY + $ROW_H);
}

// Empty state
if (count($reports) === 0) {
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell($pdf->usableWidth(), 10, 'No reports found matching the current filters.', 0, 1, 'C');
}

// ── OUTPUT ───────────────────────────────────────────────────
$pdf->Output('D', 'agaplink_reports_' . date('Y-m-d') . '.pdf');
exit;
