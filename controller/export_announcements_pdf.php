<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Announcement.php';

// ── Load FPDF ────────────────────────────────────────────────────────────────
// Adjust the path to wherever fpdf.php lives in your project.
// Common placements:  vendor/fpdf/fpdf.php  OR  lib/fpdf/fpdf.php
require_once ROOT_PATH . "/vendor/autoload.php";

// ── Fetch data ───────────────────────────────────────────────────────────────
$announcementModel = new Announcement();
$announcements     = $announcementModel->getAll();

// ── Custom FPDF class with header / footer ───────────────────────────────────
class AnnouncementsPDF extends FPDF
{
    // ── Page header ──────────────────────────────────────────────────────────
    public function Header()
    {
        // Background accent bar
        $this->SetFillColor(255, 107, 53); // #ff6b35    // blue-600
        $this->Rect(0, 0, 210, 18, 'F');

        // System name
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 4);
        $this->Cell(0, 10, 'AGAP-Link', 0, 0, 'L');

        // Page label on the right
        $this->SetFont('Arial', '', 9);
        $this->SetXY(0, 5);
        $this->Cell(200, 8, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');

        // Section title below the bar
      $this->SetTextColor(26, 35, 50); // dark        // slate-800
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(10, 24);
        $this->Cell(0, 10, 'Announcements Report', 0, 1, 'L');

        // Generated timestamp
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);        // slate-500
        $this->SetX(10);
        $this->Cell(0, 6, 'Generated: ' . date('F d, Y  h:i A'), 0, 1, 'L');

        // Divider line
        $this->SetDrawColor(37, 99, 235);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY() + 1, 200, $this->GetY() + 1);
        $this->Ln(5);
    }

    // ── Page footer ──────────────────────────────────────────────────────────
    public function Footer()
    {
        $this->SetY(-12);
        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(1);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(95, 6, 'AGAP-Link Community Platform', 0, 0, 'L');
        $this->Cell(95, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }

    // ── Safe multi-line cell (latin-1 safe) ──────────────────────────────────
    public function SafeMultiCell($w, $h, $txt, $border = 0, $align = 'L', $fill = false)
    {
        $txt = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $txt);
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    // ── Safe single cell ─────────────────────────────────────────────────────
    public function SafeCell($w, $h, $txt, $border = 0, $ln = 0, $align = 'L', $fill = false)
    {
        $txt = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $txt);
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill);
    }
}

// ── Build PDF ────────────────────────────────────────────────────────────────
$pdf = new AnnouncementsPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAuthor('AGAP-Link Admin');
$pdf->SetTitle('Announcements Report');
$pdf->SetCreator('AGAP-Link');
$pdf->SetMargins(10, 50, 10);   // left, top (leaves room for header), right
$pdf->AddPage();

// ── Summary box ──────────────────────────────────────────────────────────────
$pdf->SetFillColor(255, 237, 230); // light orange bg
$pdf->SetDrawColor(255, 107, 53);  // primary border
$pdf->SetLineWidth(0.3);
$pdf->Rect(10, $pdf->GetY(), 190, 14, 'DF'); // needs custom or use Rect

// Fallback if RoundedRect not available — plain rect
// $pdf->Rect(10, $pdf->GetY(), 190, 14, 'DF');

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(29, 78, 216);   // blue-700
$pdf->SetX(14);
$pdf->SafeCell(0, 14, 'Total Announcements Published:  ' . count($announcements), 0, 1, 'L');
$pdf->Ln(4);

// ── Table header row ─────────────────────────────────────────────────────────
$colWidths = [8, 55, 25, 30, 72];   // #, Title, Category, Date, Content preview

$pdf->SetFillColor(44, 62, 80); // secondary (dark blue-gray)
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetLineWidth(0);

$headers = ['#', 'Title', 'Category', 'Date', 'Content Preview'];
foreach ($headers as $i => $h) {
    $pdf->SafeCell($colWidths[$i], 8, $h, 0, 0, 'C', true);
}
$pdf->Ln();

// ── Table rows ───────────────────────────────────────────────────────────────
$rowNum  = 0;
$oddFill  = [248, 250, 252];   // slate-50
$evenFill = [255, 255, 255];

foreach ($announcements as $a) {
    $rowNum++;
    $isOdd = ($rowNum % 2 !== 0);

    // Truncate content preview to ~120 chars
    $preview = strip_tags($a['content']);
    if (mb_strlen($preview) > 120) {
        $preview = mb_substr($preview, 0, 117) . '...';
    }

    $category   = $a['category']    ?? '—';
    $dateStr    = date('M d, Y', strtotime($a['created_at']));
    $author     = $a['author_name'] ?? 'Admin';

    // Estimate row height based on content (MultiCell height)
    $lineH  = 5;
    $fill   = $isOdd ? $oddFill : $evenFill;
    $pdf->SetFillColor($fill[0], $fill[1], $fill[2]);
    $pdf->SetTextColor(55, 65, 81); // gray-800
    $pdf->SetFont('Arial', '', 8);

    // Save Y before row, draw each cell
    $xStart  = $pdf->GetX();
    $yStart  = $pdf->GetY();

    // Row number
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->SetXY($xStart, $yStart);
    $pdf->Cell($colWidths[0], $lineH, $rowNum, 0, 0, 'C', true);

    // Title (may wrap — measure first with GetStringWidth)
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(15, 23, 42);
    $pdf->SetXY($xStart + $colWidths[0], $yStart);
    $titleLines = ceil($pdf->GetStringWidth($a['title']) / ($colWidths[1] - 4));
    $pdf->SafeMultiCell($colWidths[1], $lineH, $a['title'], 0, 'L', true);

    // Figure out actual height used by title
    $yAfterTitle = $pdf->GetY();
    $rowH = max($yAfterTitle - $yStart, $lineH);

    // Category
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 41, 59);
    $pdf->SetXY($xStart + $colWidths[0] + $colWidths[1], $yStart);
    $pdf->SafeCell($colWidths[2], $rowH, $category, 0, 0, 'C', true);

    // Date + author
    $pdf->SetXY($xStart + $colWidths[0] + $colWidths[1] + $colWidths[2], $yStart);
    $pdf->SafeMultiCell($colWidths[3], $lineH, $dateStr . "\n" . $author, 0, 'C', true);

    // Content preview
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetTextColor(71, 85, 105);
    $pdf->SetXY($xStart + $colWidths[0] + $colWidths[1] + $colWidths[2] + $colWidths[3], $yStart);
    $pdf->SafeMultiCell($colWidths[4], $lineH, $preview, 0, 'L', true);

    // Advance past the tallest cell
    $yEnd = max(
        $yAfterTitle,
        $xStart + $colWidths[0] + $colWidths[1] + $colWidths[2] + $colWidths[3],
        $pdf->GetY()
    );

    // Thin separator line
    $pdf->SetDrawColor(203, 213, 225);
    $pdf->SetLineWidth(0.2);
    $curY = $pdf->GetY();
    $pdf->Line(10, $curY, 200, $curY);
}

$pdf->Ln(6);

// ── Footer note ──────────────────────────────────────────────────────────────
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(148, 163, 184);
$pdf->SafeCell(0, 5, 'This report was automatically generated by AGAP-Link. All times are server-local.', 0, 1, 'C');

// ── Stream to browser ────────────────────────────────────────────────────────
$filename = 'announcements_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $filename);   // 'D' = force download  |  'I' = inline preview
exit();