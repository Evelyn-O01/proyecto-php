<?php

namespace App\Backend\Views\Engine;

/**
 * Generador de documentos PDF en PHP puro (sin dependencias externas).
 *
 * Dibuja texto con las fuentes base del estándar PDF (Helvetica), formas
 * básicas y tablas, con salto de página automático y numeración al pie.
 * Las coordenadas de la API son desde la esquina superior izquierda.
 */
class PdfDocument
{
    public const FONT_REGULAR = 'F1';
    public const FONT_BOLD = 'F2';
    public const FONT_ITALIC = 'F3';
    public const FONT_MONO = 'F4';

    private const HELVETICA_WIDTHS = [
        32 => 278, 33 => 278, 34 => 355, 35 => 556, 36 => 556, 37 => 889, 38 => 667, 39 => 191,
        40 => 333, 41 => 333, 42 => 389, 43 => 584, 44 => 278, 45 => 333, 46 => 278, 47 => 278,
        48 => 556, 49 => 556, 50 => 556, 51 => 556, 52 => 556, 53 => 556, 54 => 556, 55 => 556,
        56 => 556, 57 => 556, 58 => 278, 59 => 278, 60 => 584, 61 => 584, 62 => 584, 63 => 556,
        64 => 1015, 65 => 667, 66 => 667, 67 => 722, 68 => 722, 69 => 667, 70 => 611, 71 => 778,
        72 => 722, 73 => 278, 74 => 500, 75 => 667, 76 => 556, 77 => 833, 78 => 722, 79 => 778,
        80 => 667, 81 => 778, 82 => 722, 83 => 667, 84 => 611, 85 => 722, 86 => 667, 87 => 944,
        88 => 667, 89 => 667, 90 => 611, 91 => 278, 92 => 278, 93 => 278, 94 => 469, 95 => 556,
        96 => 333, 97 => 556, 98 => 556, 99 => 500, 100 => 556, 101 => 556, 102 => 278, 103 => 556,
        104 => 556, 105 => 222, 106 => 222, 107 => 500, 108 => 222, 109 => 833, 110 => 556,
        111 => 556, 112 => 556, 113 => 556, 114 => 333, 115 => 500, 116 => 278, 117 => 556,
        118 => 500, 119 => 722, 120 => 500, 121 => 500, 122 => 500, 123 => 334, 124 => 260,
        125 => 334, 126 => 584,
    ];

    private const HELVETICA_BOLD_WIDTHS = [
        32 => 278, 33 => 333, 34 => 474, 35 => 556, 36 => 556, 37 => 889, 38 => 722, 39 => 238,
        40 => 333, 41 => 333, 42 => 389, 43 => 584, 44 => 278, 45 => 333, 46 => 278, 47 => 278,
        48 => 556, 49 => 556, 50 => 556, 51 => 556, 52 => 556, 53 => 556, 54 => 556, 55 => 556,
        56 => 556, 57 => 556, 58 => 333, 59 => 333, 60 => 584, 61 => 584, 62 => 584, 63 => 611,
        64 => 975, 65 => 722, 66 => 722, 67 => 722, 68 => 722, 69 => 667, 70 => 611, 71 => 778,
        72 => 722, 73 => 278, 74 => 556, 75 => 722, 76 => 611, 77 => 833, 78 => 722, 79 => 778,
        80 => 667, 81 => 778, 82 => 722, 83 => 667, 84 => 611, 85 => 722, 86 => 667, 87 => 944,
        88 => 667, 89 => 667, 90 => 611, 91 => 333, 92 => 278, 93 => 333, 94 => 584, 95 => 556,
        96 => 333, 97 => 556, 98 => 611, 99 => 556, 100 => 611, 101 => 556, 102 => 333, 103 => 611,
        104 => 611, 105 => 278, 106 => 278, 107 => 556, 108 => 278, 109 => 889, 110 => 611,
        111 => 611, 112 => 611, 113 => 611, 114 => 389, 115 => 556, 116 => 333, 117 => 611,
        118 => 556, 119 => 778, 120 => 556, 121 => 556, 122 => 500, 123 => 389, 124 => 280,
        125 => 389, 126 => 584,
    ];

    private float $pageWidth;
    private float $pageHeight;
    private float $marginLeft = 42.0;
    private float $marginTop = 40.0;
    private float $marginRight = 42.0;
    private float $marginBottom = 54.0;
    private float $cursorY = 0.0;

    /** @var string[] Contenido de cada página. */
    private array $pages = [];

    private string $title;
    private string $author;
    private string $subject;

    public function __construct(
        string $title = 'Documento',
        string $author = 'AURA Atelier',
        string $subject = '',
        float $pageWidth = 595.28,
        float $pageHeight = 841.89
    ) {
        $this->title = $title;
        $this->author = $author;
        $this->subject = $subject;
        $this->pageWidth = $pageWidth;
        $this->pageHeight = $pageHeight;
    }

    public function getPageWidth(): float
    {
        return $this->pageWidth;
    }

    public function getPageHeight(): float
    {
        return $this->pageHeight;
    }

    public function getContentWidth(): float
    {
        return $this->pageWidth - $this->marginLeft - $this->marginRight;
    }

    public function getLeft(): float
    {
        return $this->marginLeft;
    }

    public function getRight(): float
    {
        return $this->pageWidth - $this->marginRight;
    }

    public function getBottom(): float
    {
        return $this->pageHeight - $this->marginBottom;
    }

    public function getY(): float
    {
        return $this->cursorY;
    }

    public function setY(float $y): void
    {
        $this->cursorY = $y;
    }

    public function moveY(float $delta): void
    {
        $this->cursorY += $delta;
    }

    public function getPageCount(): int
    {
        return count($this->pages);
    }

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->cursorY = $this->marginTop;
    }

    /**
     * Garantiza que quepan $height puntos en la página actual.
     * Devuelve true si fue necesario abrir una página nueva.
     */
    public function ensureSpace(float $height, ?float $currentY = null): bool
    {
        if (empty($this->pages)) {
            $this->addPage();
            return true;
        }

        $y = $currentY ?? $this->cursorY;

        if ($y + $height > $this->getBottom()) {
            $this->addPage();
            return true;
        }

        return false;
    }

    public function measure(string $text, float $size, string $font = self::FONT_REGULAR): float
    {
        if ($text === '') {
            return 0.0;
        }

        $encoded = $this->encodeText($text, false);
        $total = 0.0;

        for ($i = 0, $len = strlen($encoded); $i < $len; $i++) {
            $total += $this->glyphWidth(ord($encoded[$i]), $font);
        }

        return ($total / 1000) * $size;
    }

    /**
     * Escribe texto con ajuste de línea opcional.
     *
     * @param array{0: float, 1: float, 2: float} $color
     * @return float Altura total ocupada por el bloque de texto.
     */
    public function text(
        string $text,
        float $x,
        float $y,
        float $size = 9.0,
        string $font = self::FONT_REGULAR,
        array $color = [0.0, 0.0, 0.0],
        ?float $maxWidth = null,
        string $align = 'left',
        ?float $lineHeight = null
    ): float {
        [$ops, $height] = $this->buildTextOps($text, $x, $y, $size, $font, $color, $maxWidth, $align, $lineHeight);
        $this->append($ops);

        return $height;
    }

    /**
     * @param array{0: float, 1: float, 2: float} $color
     */
    public function fillRect(float $x, float $y, float $width, float $height, array $color): void
    {
        $this->append(sprintf(
            "%s rg\n%s re f\n",
            $this->color($color),
            $this->rect($x, $y, $width, $height)
        ));
    }

    /**
     * @param array{0: float, 1: float, 2: float} $color
     */
    public function strokeRect(float $x, float $y, float $width, float $height, array $color, float $lineWidth = 0.6): void
    {
        $this->append(sprintf(
            "%s RG\n%.3F w\n%s re S\n",
            $this->color($color),
            $lineWidth,
            $this->rect($x, $y, $width, $height)
        ));
    }

    /**
     * @param array{0: float, 1: float, 2: float} $color
     */
    public function line(float $x1, float $y1, float $x2, float $y2, array $color, float $lineWidth = 0.6): void
    {
        $this->append(sprintf(
            "%s RG\n%.3F w\n%.3F %.3F m %.3F %.3F l S\n",
            $this->color($color),
            $lineWidth,
            $x1,
            $this->pdfY($y1),
            $x2,
            $this->pdfY($y2)
        ));
    }

    public function finish(): string
    {
        if (empty($this->pages)) {
            $this->addPage();
        }

        $totalPages = count($this->pages);
        $footerColor = [0.45, 0.43, 0.41];
        $ruleColor = [0.86, 0.84, 0.81];

        foreach ($this->pages as $index => $content) {
            $pageNumber = $index + 1;
            $footerTop = $this->pageHeight - 36.0;

            $footerRule = sprintf(
                "%s RG\n0.6 w\n%.3F %.3F m %.3F %.3F l S\n",
                $this->color($ruleColor),
                $this->marginLeft,
                $this->pdfY($footerTop),
                $this->getRight(),
                $this->pdfY($footerTop)
            );

            [$leftOps] = $this->buildTextOps(
                'AURA Atelier · Documento confidencial · Generado por el sistema de reportes',
                $this->marginLeft,
                $footerTop + 8.0,
                6.8,
                self::FONT_REGULAR,
                $footerColor
            );

            [$rightOps] = $this->buildTextOps(
                'Página ' . $pageNumber . ' de ' . $totalPages,
                $this->getRight(),
                $footerTop + 8.0,
                6.8,
                self::FONT_BOLD,
                $footerColor,
                null,
                'right'
            );

            $this->pages[$index] = $content . $footerRule . $leftOps . $rightOps;
        }

        return $this->buildFile();
    }

    private function append(string $ops): void
    {
        if (empty($this->pages)) {
            $this->addPage();
        }

        $this->pages[count($this->pages) - 1] .= $ops;
    }

    /**
     * @param array{0: float, 1: float, 2: float} $color
     * @return array{0: string, 1: float}
     */
    private function buildTextOps(
        string $text,
        float $x,
        float $y,
        float $size,
        string $font,
        array $color,
        ?float $maxWidth = null,
        string $align = 'left',
        ?float $lineHeight = null
    ): array {
        $lineHeight = $lineHeight ?? ($size * 1.32);
        $lines = $maxWidth !== null
            ? $this->wrapText($text, $size, $font, $maxWidth)
            : explode("\n", $text);

        $ops = '';
        $colorOp = $this->color($color) . " rg\n";

        foreach ($lines as $index => $line) {
            if ($line === '') {
                continue;
            }

            $lineWidth = $this->measure($line, $size, $font);
            $drawX = match ($align) {
                'center' => $x - ($lineWidth / 2),
                'right' => $x - $lineWidth,
                default => $x,
            };

            $baseline = $y + ($size * 0.80) + ($index * $lineHeight);

            $ops .= $colorOp . sprintf(
                "BT /%s %.2F Tf 1 0 0 1 %.3F %.3F Tm (%s) Tj ET\n",
                $font,
                $size,
                $drawX,
                $this->pdfY($baseline),
                $this->encodeText($line)
            );
        }

        return [$ops, count($lines) * $lineHeight];
    }

    /**
     * @return string[]
     */
    private function wrapText(string $text, float $size, string $font, float $maxWidth): array
    {
        $lines = [];
        $paragraphs = preg_split('/\r\n|\r|\n/', $text) ?: [''];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }

            $words = preg_split('/\s+/u', $paragraph) ?: [];
            $current = '';

            foreach ($words as $word) {
                $candidate = $current === '' ? $word : $current . ' ' . $word;

                if ($this->measure($candidate, $size, $font) <= $maxWidth) {
                    $current = $candidate;
                    continue;
                }

                if ($current !== '') {
                    $lines[] = $current;
                    $current = '';
                }

                // Palabra individual más ancha que el espacio disponible.
                while ($this->measure($word, $size, $font) > $maxWidth) {
                    $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    if (count($chars) <= 1) {
                        break;
                    }

                    $chunk = '';
                    $cutAt = 0;
                    foreach ($chars as $index => $char) {
                        if ($this->measure($chunk . $char, $size, $font) > $maxWidth && $chunk !== '') {
                            break;
                        }
                        $chunk .= $char;
                        $cutAt = $index + 1;
                    }

                    if ($chunk === '') {
                        break;
                    }

                    $lines[] = $chunk;
                    $word = implode('', array_slice($chars, $cutAt));
                }

                $current = $word;
            }

            if ($current !== '') {
                $lines[] = $current;
            }
        }

        return $lines === [] ? [''] : $lines;
    }

    private function glyphWidth(int $code, string $font): int
    {
        if ($font === self::FONT_MONO) {
            return 600;
        }

        $map = $font === self::FONT_BOLD ? self::HELVETICA_BOLD_WIDTHS : self::HELVETICA_WIDTHS;

        return $map[$code] ?? 556;
    }

    private function encodeText(string $text, bool $escape = true): string
    {
        $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);

        if ($encoded === false) {
            $encoded = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
        }

        if (!$escape) {
            return $encoded;
        }

        $result = '';
        for ($i = 0, $len = strlen($encoded); $i < $len; $i++) {
            $char = $encoded[$i];
            $code = ord($char);

            if ($char === '\\' || $char === '(' || $char === ')') {
                $result .= '\\' . $char;
            } elseif ($code < 32 || $code > 126) {
                $result .= sprintf('\\%03o', $code);
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    private function pdfY(float $y): float
    {
        return $this->pageHeight - $y;
    }

    private function rect(float $x, float $y, float $width, float $height): string
    {
        return sprintf('%.3F %.3F %.3F %.3F', $x, $this->pdfY($y + $height), $width, $height);
    }

    /**
     * @param array{0: float, 1: float, 2: float} $color
     */
    private function color(array $color): string
    {
        return sprintf('%.3F %.3F %.3F', $color[0], $color[1], $color[2]);
    }

    /**
     * Mapa ToUnicode compartido por las fuentes WinAnsi para que el texto
     * del PDF se pueda copiar y buscar correctamente (acentos incluidos).
     */
    private function buildToUnicodeCMap(): string
    {
        $entries = [];

        for ($code = 32; $code <= 255; $code++) {
            $utf8 = @iconv('Windows-1252', 'UTF-8', chr($code));

            if ($utf8 === false || $utf8 === '') {
                continue;
            }

            $utf16 = @iconv('UTF-8', 'UTF-16BE', $utf8);

            if ($utf16 === false || $utf16 === '') {
                continue;
            }

            $entries[] = sprintf('%02X <%s>', $code, strtoupper(bin2hex($utf16)));
        }

        $cmap = "/CIDInit /ProcSet findresource begin\n";
        $cmap .= "12 dict begin\n";
        $cmap .= "begincmap\n";
        $cmap .= "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n";
        $cmap .= "/CMapName /Adobe-Identity-UCS def\n";
        $cmap .= "/CMapType 2 def\n";
        $cmap .= "1 begincodespacerange\n<00> <FF>\nendcodespacerange\n";

        foreach (array_chunk($entries, 100) as $chunk) {
            $cmap .= count($chunk) . " beginbfchar\n";
            foreach ($chunk as $entry) {
                $cmap .= $entry . "\n";
            }
            $cmap .= "endbfchar\n";
        }

        $cmap .= "endcmap\n";
        $cmap .= "CMapName currentdict /CMap defineresource pop\n";
        $cmap .= "end\nend\n";

        return '<< /Length ' . strlen($cmap) . " >>\nstream\n" . $cmap . 'endstream';
    }

    private function buildFile(): string
    {
        $pageCount = count($this->pages);
        $objects = [];

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $kids[] = (9 + ($i * 2)) . ' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $pageCount . ' >>';

        $objects[3] = sprintf(
            '<< /Title (%s) /Author (%s) /Subject (%s) /Creator (AURA Atelier) /Producer (AURA Atelier PDF Engine) /CreationDate (D:%s) >>',
            $this->encodeText($this->title),
            $this->encodeText($this->author),
            $this->encodeText($this->subject),
            date('YmdHis')
        );

        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding /ToUnicode 8 0 R >>';
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding /ToUnicode 8 0 R >>';
        $objects[6] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding /ToUnicode 8 0 R >>';
        $objects[7] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding /ToUnicode 8 0 R >>';
        $objects[8] = $this->buildToUnicodeCMap();

        foreach ($this->pages as $index => $content) {
            $pageId = 9 + ($index * 2);
            $contentId = $pageId + 1;

            $objects[$contentId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . 'endstream';
            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R /F4 7 0 R >> >> /Contents %d 0 R >>',
                $this->pageWidth,
                $this->pageHeight,
                $contentId
            );
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $objectCount = max(array_keys($objects)) + 1;

        $pdf .= "xref\n0 " . $objectCount . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($id = 1; $id < $objectCount; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }

        $pdf .= "trailer\n<< /Size " . $objectCount . " /Root 1 0 R /Info 3 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }
}
