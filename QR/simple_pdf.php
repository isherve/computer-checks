<?php
/**
 * Minimal PDF writer (no TCPDF / GD) – suitable for Vercel serverless.
 */
class SimpleReportPdf
{
    private array $pages = [];
    private int $page = 0;
    private float $width;
    private float $height;
    private float $y;
    private float $margin = 36;
    private string $docTitle = 'Report';

    public function __construct(bool $landscape = false)
    {
        $this->width = $landscape ? 841.89 : 595.28;
        $this->height = $landscape ? 595.28 : 841.89;
        $this->addPage();
    }

    public function setTitle(string $title): void
    {
        $this->docTitle = $title;
    }

    private function addPage(): void
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->y = $this->height - $this->margin;
    }

    private function out(string $s): void
    {
        $this->pages[$this->page] .= $s . "\n";
    }

    private function escape(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function sanitize(string $s): string
    {
        $s = preg_replace('/[^\x20-\x7E]/', '?', $s) ?? $s;
        return trim(preg_replace('/\s+/', ' ', $s) ?? $s);
    }

    public function heading(string $text, float $size = 14): void
    {
        $text = $this->sanitize($text);
        $this->ensureSpace($size + 10);
        $tw = strlen($text) * $size * 0.45;
        $x = max($this->margin, ($this->width - $tw) / 2);
        $this->out('BT /F1 ' . $size . ' Tf ' . sprintf('%.2F %.2F', $x, $this->y) . ' Td (' . $this->escape($text) . ') Tj ET');
        $this->y -= $size + 8;
    }

    public function line(string $text, float $size = 9): void
    {
        $text = $this->sanitize($text);
        $this->ensureSpace($size + 6);
        $this->out('BT /F1 ' . $size . ' Tf ' . sprintf('%.2F %.2F', $this->margin, $this->y) . ' Td (' . $this->escape($text) . ') Tj ET');
        $this->y -= $size + 6;
    }

    public function ensureSpace(float $need): void
    {
        if ($this->y - $need < $this->margin) {
            $this->addPage();
        }
    }

    public function table(array $headers, array $rows, float $fontSize = 7.5): void
    {
        $cols = max(1, count($headers));
        $usable = $this->width - 2 * $this->margin;
        $colW = $usable / $cols;
        $rowH = $fontSize + 7;

        $drawRow = function (array $cells, bool $header) use ($cols, $colW, $rowH, $fontSize): void {
            $this->ensureSpace($rowH + 2);
            $x = $this->margin;
            $yBottom = $this->y - $rowH;

            for ($i = 0; $i < $cols; $i++) {
                $cell = $this->sanitize((string)($cells[$i] ?? ''));
                $maxChars = max(3, (int)floor($colW / ($fontSize * 0.48)) - 1);
                if (strlen($cell) > $maxChars) {
                    $cell = substr($cell, 0, max(1, $maxChars - 1)) . '?';
                }

                if ($header) {
                    $this->out('0.85 g ' . sprintf('%.2F %.2F %.2F %.2F re f 0 g', $x, $yBottom, $colW, $rowH));
                }
                $this->out(sprintf('%.2F %.2F %.2F %.2F re S', $x, $yBottom, $colW, $rowH));
                $this->out(
                    'BT /F1 ' . $fontSize . ' Tf ' .
                    sprintf('%.2F %.2F', $x + 2, $yBottom + 2.5) .
                    ' Td (' . $this->escape($cell) . ') Tj ET'
                );
                $x += $colW;
            }
            $this->y = $yBottom - 1;
        };

        $drawRow($headers, true);
        foreach ($rows as $row) {
            $drawRow(array_values($row), false);
        }
    }

    public function outputDownload(string $filename): void
    {
        $pdf = $this->build();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdf;
    }

    private function build(): string
    {
        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objs[4] = '<< /Title (' . $this->escape($this->sanitize($this->docTitle)) . ') /Creator (Computer Checks) /Author (UTBrubavu) >>';

        $kidRefs = [];
        $next = 5;
        foreach ($this->pages as $content) {
            $contentId = $next++;
            $pageId = $next++;
            $objs[$contentId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objs[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R >> >> /Contents %d 0 R >>',
                $this->width,
                $this->height,
                $contentId
            );
            $kidRefs[] = $pageId . ' 0 R';
        }
        $objs[2] = '<< /Type /Pages /Kids [' . implode(' ', $kidRefs) . '] /Count ' . count($kidRefs) . ' >>';

        ksort($objs);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $size = max(array_keys($objs)) + 1;
        $pdf .= "xref\n0 {$size}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R /Info 4 0 R >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }
}
