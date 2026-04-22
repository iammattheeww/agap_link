<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Announcement.php';
require_once ROOT_PATH . "/vendor/autoload.php";

// ── Fetch data ────────────────────────────────────────────────
$announcementModel = new Announcement();
$announcements     = $announcementModel->getAll();

// ── PDF Class ─────────────────────────────────────────────────
class AnnouncementsPDF extends FPDF
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
        $this->Cell(0, 8, 'Announcements Report', 0, 1, 'L');

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
        $this->Cell($half, 6, 'AGAP-Link Community Platform', 0, 0, 'L');
        $this->Cell($half, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }

    // ── SAFE TEXT (UTF-8 → latin-1) ───────────────────────────
    public function safe(string $txt): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $txt);
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

    // ── TRUNCATE to fit width ─────────────────────────────────
    public function truncate(string $text, float $maxW, string $font = 'Arial', string $style = '', int $size = 8): string
    {
        $this->SetFont($font, $style, $size);
        if ($this->GetStringWidth($text) <= $maxW) return $text;
        while (mb_strlen($text) > 0 && $this->GetStringWidth($text . '...') > $maxW) {
            $text = mb_substr($text, 0, -1);
        }
        return $text . '...';
    }

    // ── MULTILINE HEIGHT ESTIMATE ─────────────────────────────
    // Returns how many mm a MultiCell will consume (approx)
    public function multiCellHeight(float $w, float $lineH, string $text, string $font = 'Arial', string $style = '', int $size = 7): float
    {
        $this->SetFont($font, $style, $size);
        $text  = $this->safe($text);
        $lines = explode("\n", $text);
        $count = 0;
        foreach ($lines as $line) {
            if ($line === '') { $count++; continue; }
            $count += max(1, ceil($this->GetStringWidth($line) / ($w - 2)));
        }
        return $count * $lineH;
    }

    // ── SAFE IMAGE ────────────────────────────────────────────
    public function safeImage(string $path, float $x, float $y, float $w, float $h): void
    {
        if (!file_exists($path) || !is_readable($path)) return;
        $info = @getimagesize($path);
        if (!$info) return;
        $ext = strtolower(image_type_to_extension($info[2], false));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return;
        try { $this->Image($path, $x, $y, $w, $h, strtoupper($ext === 'jpg' ? 'jpeg' : $ext)); }
        catch (Throwable $e) {}
    }
}

// ── INIT PDF ─────────────────────────────────────────────────
$pdf = new AnnouncementsPDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAuthor('AGAP-Link Admin');
$pdf->SetTitle('Announcements Report');
$pdf->SetMargins(10, 48, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// ── COLUMN WIDTHS  (must total 277 mm) ───────────────────────
// #(10) + Title(65) + Date(30) + Author(35) + Preview(92) + Image(45) = 277
$cols = [
    '#'       =>  10,
    'Title'   =>  65,
    'Date'    =>  30,
    'Author'  =>  35,
    'Preview' =>  92,
    'Image'   =>  45,
];

$HEADER_H = 9;
$IMG_W    = 38;   // thumbnail width  (inside Image cell)
$IMG_H    = 20;   // thumbnail height

// ── SUMMARY BOX ──────────────────────────────────────────────
$uw       = $pdf->usableWidth();  // 277 mm
$summaryY = $pdf->GetY();

$pdf->SetFillColor(255, 237, 230);
$pdf->SetDrawColor(255, 107, 53);
$pdf->SetLineWidth(0.4);
$pdf->Rect(10, $summaryY, $uw, 13, 'DF');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(29, 78, 216);
$pdf->SetXY(14, $summaryY + 3);
$pdf->Cell(100, 7, 'Total Announcements Published:  ' . count($announcements), 0, 0, 'L');

$pdf->Ln(17);

// ── TABLE HEADER ─────────────────────────────────────────────
$pdf->TableHeader($cols, $HEADER_H);

// ── ROWS ─────────────────────────────────────────────────────
$rowNum = 0;

foreach ($announcements as $a) {
    $rowNum++;

    // Content preview (strip HTML, truncate)
    $preview  = strip_tags($a['content'] ?? '');
    $preview  = preg_replace('/\s+/', ' ', trim($preview));
    if (mb_strlen($preview) > 160) $preview = mb_substr($preview, 0, 157) . '...';

    $dateStr  = date('M d, Y', strtotime($a['created_at']));
    $author   = $a['author_name'] ?? 'Admin';
    $title    = $a['title']       ?? '—';

    // Determine dynamic row height based on tallest multi-line cell
    $lineH    = 5;
    $previewH = $pdf->multiCellHeight($cols['Preview'], $lineH, $preview, 'Arial', 'I', 7);
    $titleH   = $pdf->multiCellHeight($cols['Title'],   $lineH, $title,   'Arial', 'B', 8);
    $ROW_H    = max($previewH, $titleH, $IMG_H + 4, $lineH * 2 + 2);

    // Page-break check
    if ($pdf->GetY() + $ROW_H > ($pdf->GetPageHeight() - 18)) {
        $pdf->AddPage();
        $pdf->TableHeader($cols, $HEADER_H);
    }

    // Row background
    [$bgR, $bgG, $bgB] = ($rowNum % 2 === 0) ? [255, 255, 255] : [248, 250, 252];
    $pdf->SetFillColor($bgR, $bgG, $bgB);
    $pdf->SetDrawColor(203, 213, 225);
    $pdf->SetLineWidth(0.2);

    $startX = 10;
    $startY = $pdf->GetY();

    // ── # ────────────────────────────────────────────────────
    $pdf->SetXY($startX, $startY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell($cols['#'], $ROW_H, $rowNum, 1, 0, 'C', true);

    // ── TITLE (MultiCell — vertically + horizontally centered) ──
    $curX = $startX + $cols['#'];
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(15, 23, 42);
    $pdf->Rect($curX, $startY, $cols['Title'], $ROW_H, 'DF');
    $titleTextH = $pdf->multiCellHeight($cols['Title'] - 2, $lineH, $title, 'Arial', 'B', 8);
    $titleOffY  = ($ROW_H - $titleTextH) / 2;
    $pdf->SetXY($curX + 1, $startY + max(1, $titleOffY));
    $pdf->MultiCell($cols['Title'] - 2, $lineH, $pdf->safe($title), 0, 'C', false);

    // ── DATE ─────────────────────────────────────────────────
    $curX += $cols['Title'];
    $pdf->SetXY($curX, $startY);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(30, 41, 59);
    $pdf->Cell($cols['Date'], $ROW_H, $dateStr, 1, 0, 'C', true);

    // ── AUTHOR ───────────────────────────────────────────────
    $curX += $cols['Date'];
    $pdf->SetXY($curX, $startY);
    $pdf->SetFont('Arial', '', 7);
    $auth = $pdf->truncate($pdf->safe($author), $cols['Author'] - 3, 'Arial', '', 7);
    $pdf->Cell($cols['Author'], $ROW_H, $auth, 1, 0, 'C', true);

    // ── PREVIEW (MultiCell — vertically + horizontally centered) ─
    $curX += $cols['Author'];
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetTextColor(71, 85, 105);
    $pdf->Rect($curX, $startY, $cols['Preview'], $ROW_H, 'DF');
    $previewTextH = $pdf->multiCellHeight($cols['Preview'] - 2, $lineH, $preview, 'Arial', 'I', 7);
    $previewOffY  = ($ROW_H - $previewTextH) / 2;
    $pdf->SetXY($curX + 1, $startY + max(1, $previewOffY));
    $pdf->MultiCell($cols['Preview'] - 2, $lineH, $pdf->safe($preview), 0, 'C', false);

    // ── IMAGE CELL ───────────────────────────────────────────
    $curX += $cols['Preview'];
    $pdf->SetXY($curX, $startY);
    $pdf->SetFillColor($bgR, $bgG, $bgB);
    $pdf->Cell($cols['Image'], $ROW_H, '', 1, 0, 'C', true);  // bordered placeholder

    // Build image path
    $imgPath = null;
    if (!empty($a['image_path'])) {
        // Try DOCUMENT_ROOT + image_path (absolute web path stored in DB)
        $candidate = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($a['image_path'], '/');
        if (file_exists($candidate)) {
            $imgPath = $candidate;
        } else {
            // Fallback: treat as filesystem path directly
            $candidate2 = rtrim(ROOT_PATH, '/') . '/uploads/announcements/' . basename($a['image_path']);
            if (file_exists($candidate2)) $imgPath = $candidate2;
        }
    }

    if ($imgPath) {
        $imgX = $curX + ($cols['Image'] - $IMG_W) / 2;
        $imgY = $startY + ($ROW_H - $IMG_H) / 2;
        $pdf->safeImage($imgPath, $imgX, $imgY, $IMG_W, $IMG_H);
    } else {
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->SetTextColor(156, 163, 175);
        $pdf->SetXY($curX, $startY + $ROW_H / 2 - 2);
        $pdf->Cell($cols['Image'], 4, 'No image', 0, 0, 'C');
    }

    // ── Advance to next row ───────────────────────────────────
    $pdf->SetXY($startX, $startY + $ROW_H);
}

// Empty state
if (count($announcements) === 0) {
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell($pdf->usableWidth(), 10, 'No announcements have been published yet.', 0, 1, 'C');
}

// ── Footer note ───────────────────────────────────────────────
$pdf->Ln(6);
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(148, 163, 184);
$pdf->SetX(10);
$pdf->Cell($pdf->usableWidth(), 5,
    'This report was automatically generated by AGAP-Link. All times are server-local.',
    0, 1, 'C');

// ── Output ────────────────────────────────────────────────────
$pdf->Output('D', 'announcements_' . date('Ymd_His') . '.pdf');
exit();