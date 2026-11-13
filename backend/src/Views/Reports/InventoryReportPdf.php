<?php

namespace App\Backend\Views\Reports;

use App\Backend\Views\Engine\PdfDocument;

/**
 * Construye el PDF del Reporte de Inventario y Stock Crítico a partir de los
 * datos que entrega ReportController::getInventoryReport().
 */
class InventoryReportPdf
{
    private const INK = [0.12, 0.11, 0.10];
    private const STONE = [0.45, 0.43, 0.41];
    private const MUTED = [0.58, 0.55, 0.52];
    private const LIGHT = [0.968, 0.964, 0.956];
    private const TRACK = [0.905, 0.892, 0.875];
    private const BORDER = [0.865, 0.850, 0.825];
    private const ACCENT = [0.960, 0.650, 0.080];
    private const WHITE = [1.0, 1.0, 1.0];
    private const DANGER = [0.760, 0.130, 0.200];
    private const DANGER_BG = [0.995, 0.945, 0.945];
    private const DANGER_BORDER = [0.950, 0.790, 0.800];
    private const AMBER_TEXT = [0.720, 0.470, 0.040];

    public static function render(array $data): string
    {
        $pdf = new PdfDocument(
            $data['title'] ?? 'Reporte de Inventario y Stock Crítico | AURA Atelier',
            'AURA Atelier',
            'Reporte de inventario y existencias'
        );

        $pdf->addPage();

        $y = self::drawHeader($pdf, $data);
        $y = self::drawMeta($pdf, $data, $y);
        $y = self::drawKpis($pdf, $data, $y);
        $y = self::drawCategories($pdf, $data, $y);
        $y = self::drawLowStock($pdf, $data, $y);
        $y = self::drawInventory($pdf, $data, $y);
        self::drawSignature($pdf, $data, $y);

        return $pdf->finish();
    }

    private static function drawHeader(PdfDocument $pdf, array $data): float
    {
        $pageWidth = $pdf->getPageWidth();
        $right = $pdf->getRight();

        $pdf->fillRect(0, 0, $pageWidth, 100, self::INK);

        $pdf->text('AURA', $pdf->getLeft(), 20, 27, PdfDocument::FONT_BOLD, self::WHITE);
        $pdf->line($pdf->getLeft() + 74, 30, $pdf->getLeft() + 74, 62, [0.35, 0.33, 0.30], 0.8);

        $pdf->text(
            'ATELIER & BOUTIQUES S.A. DE C.V.',
            $pdf->getLeft() + 86,
            30,
            6.8,
            PdfDocument::FONT_BOLD,
            [0.72, 0.69, 0.65]
        );

        $pdf->text(
            'Reporte de Inventario & Stock Crítico',
            $pdf->getLeft() + 86,
            43,
            14.5,
            PdfDocument::FONT_BOLD,
            self::WHITE
        );

        $pdf->text(
            'RPT-INV-' . date('Ymd'),
            $right,
            27,
            7.8,
            PdfDocument::FONT_BOLD,
            self::ACCENT,
            null,
            'right'
        );

        $pdf->text(
            'Corte: ' . ($data['generated_at'] ?? date('d/m/Y H:i:s')),
            $right,
            41,
            6.8,
            PdfDocument::FONT_REGULAR,
            [0.78, 0.75, 0.71],
            null,
            'right'
        );

        $pdf->text(
            'Moneda: MXN · Pesos Mexicanos',
            $right,
            53,
            6.8,
            PdfDocument::FONT_REGULAR,
            [0.78, 0.75, 0.71],
            null,
            'right'
        );

        return 122.0;
    }

    private static function drawMeta(PdfDocument $pdf, array $data, float $y): float
    {
        $left = $pdf->getLeft();
        $width = $pdf->getContentWidth();
        $height = 42.0;

        $pdf->fillRect($left, $y, $width, $height, self::LIGHT);
        $pdf->strokeRect($left, $y, $width, $height, self::BORDER, 0.5);

        $items = [
            ['FECHA DE CORTE', $data['generated_at'] ?? date('d/m/Y H:i:s')],
            ['MONEDA', 'MXN · Pesos Mexicanos'],
            ['CATEGORÍA', self::selectedCategoryName($data)],
            ['AUDITOR', $data['user']['name'] ?? 'Auditoría Almacén'],
        ];

        $columnWidth = $width / count($items);

        foreach ($items as $index => $item) {
            $x = $left + ($index * $columnWidth);

            if ($index > 0) {
                $pdf->line($x, $y + 8, $x, $y + $height - 8, self::BORDER, 0.5);
            }

            $pdf->text($item[0], $x + 12, $y + 8, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
            $pdf->text(
                self::fitText($pdf, $item[1], 7.6, PdfDocument::FONT_BOLD, $columnWidth - 24),
                $x + 12,
                $y + 19,
                7.6,
                PdfDocument::FONT_BOLD,
                self::INK
            );
        }

        return $y + $height + 16;
    }

    private static function drawKpis(PdfDocument $pdf, array $data, float $y): float
    {
        $summary = $data['summary'] ?? [];
        $left = $pdf->getLeft();
        $width = $pdf->getContentWidth();
        $gap = 10.0;
        $cardWidth = ($width - ($gap * 3)) / 4;
        $cardHeight = 66.0;
        $lowStock = (int)($summary['low_stock_count'] ?? 0);

        $cards = [
            [
                'label' => 'VALOR DE INVENTARIO',
                'value' => '$' . number_format((float)($summary['total_inventory_value'] ?? 0), 2),
                'sub' => 'MXN costo retail',
                'style' => 'dark',
            ],
            [
                'label' => 'PIEZAS FÍSICAS DISPONIBLES',
                'value' => number_format((int)($summary['total_units'] ?? 0)),
                'sub' => 'Todas las tallas / colores',
                'style' => 'light',
            ],
            [
                'label' => 'MODELOS / VARIANTES',
                'value' => (int)($summary['total_products'] ?? 0) . ' / ' . (int)($summary['total_variants'] ?? 0),
                'sub' => 'Modelos / SKUs activos',
                'style' => 'light',
            ],
            [
                'label' => 'VARIANTES EN STOCK CRÍTICO',
                'value' => (string)$lowStock,
                'sub' => 'Requieren resurtido urgente',
                'style' => $lowStock > 0 ? 'danger' : 'light',
            ],
        ];

        foreach ($cards as $index => $card) {
            $x = $left + ($index * ($cardWidth + $gap));
            $valueSize = self::fitSize($pdf, $card['value'], 15.5, 9.0, $cardWidth - 24, PdfDocument::FONT_BOLD);

            if ($card['style'] === 'dark') {
                $pdf->fillRect($x, $y, $cardWidth, $cardHeight, self::INK);
                $labelColor = self::ACCENT;
                $valueColor = self::WHITE;
                $subColor = [0.65, 0.62, 0.58];
            } elseif ($card['style'] === 'danger') {
                $pdf->fillRect($x, $y, $cardWidth, $cardHeight, self::DANGER_BG);
                $pdf->strokeRect($x, $y, $cardWidth, $cardHeight, self::DANGER_BORDER, 0.6);
                $labelColor = self::DANGER;
                $valueColor = self::DANGER;
                $subColor = self::DANGER;
            } else {
                $pdf->fillRect($x, $y, $cardWidth, $cardHeight, self::LIGHT);
                $pdf->strokeRect($x, $y, $cardWidth, $cardHeight, self::BORDER, 0.5);
                $labelColor = self::MUTED;
                $valueColor = self::INK;
                $subColor = self::MUTED;
            }

            $pdf->text($card['label'], $x + 12, $y + 10, 6.0, PdfDocument::FONT_BOLD, $labelColor, $cardWidth - 24);
            $pdf->text($card['value'], $x + 12, $y + 24, $valueSize, PdfDocument::FONT_BOLD, $valueColor, $cardWidth - 24);
            $pdf->text($card['sub'], $x + 12, $y + 50, 6.0, PdfDocument::FONT_REGULAR, $subColor, $cardWidth - 24);
        }

        return $y + $cardHeight + 20;
    }

    private static function drawCategories(PdfDocument $pdf, array $data, float $y): float
    {
        $categories = $data['stock_by_category'] ?? [];
        $needed = 27 + (min(2, max(1, count($categories))) * 25);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $y = self::sectionTitle($pdf, 'Distribución de Inventario por Categoría', $y);
        $left = $pdf->getLeft();
        $width = $pdf->getContentWidth();
        $right = $pdf->getRight();

        if (empty($categories)) {
            $pdf->text('Sin existencias registradas.', $left, $y, 8, PdfDocument::FONT_ITALIC, self::STONE);
            return $y + 26;
        }

        foreach ($categories as $category) {
            if ($pdf->ensureSpace(25, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, 'Distribución de Inventario por Categoría (continuación)', $y);
            }

            $name = self::fitText(
                $pdf,
                $category['category_name'] ?? 'Sin categoría',
                8.2,
                PdfDocument::FONT_REGULAR,
                $width - 190
            );
            $units = (int)($category['total_units'] ?? 0);
            $percentage = (float)($category['percentage'] ?? 0);
            $value = number_format($units) . ' uds   ·   $' . number_format((float)($category['stock_value'] ?? 0), 2)
                . '   (' . $percentage . '%)';

            $pdf->text($name, $left, $y, 8.2, PdfDocument::FONT_REGULAR, self::INK);
            $pdf->text($value, $right, $y, 8.2, PdfDocument::FONT_BOLD, self::INK, null, 'right');

            $pdf->fillRect($left, $y + 13, $width, 5, self::TRACK);

            if ($percentage > 0) {
                $barWidth = max(2.5, $width * min(100, $percentage) / 100);
                $pdf->fillRect($left, $y + 13, $barWidth, 5, self::INK);
            }

            $y += 25;
        }

        return $y + 6;
    }

    private static function drawLowStock(PdfDocument $pdf, array $data, float $y): float
    {
        $items = $data['low_stock_items'] ?? [];
        $threshold = (int)($data['low_stock_threshold'] ?? 10);

        if (empty($items)) {
            $needed = 27 + 22;
            if ($pdf->ensureSpace($needed, $y)) {
                $y = self::continuation($pdf);
            }
            $y = self::sectionTitle($pdf, 'Prendas con Existencia Crítica', $y);
            $pdf->text(
                'Sin variantes por debajo del umbral de ' . $threshold . ' unidades. Stock saludable.',
                $pdf->getLeft(),
                $y,
                8,
                PdfDocument::FONT_ITALIC,
                self::STONE
            );
            return $y + 26;
        }

        $title = 'Prendas con Existencia Crítica (hasta ' . $threshold . ' unidades)';
        $needed = 27 + 18 + (min(2, count($items)) * 18);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $y = self::sectionTitle($pdf, $title, $y);
        $y = self::lowStockHeader($pdf, $y);

        foreach ($items as $index => $item) {
            if ($pdf->ensureSpace(18, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, $title . ' (continuación)', $y);
                $y = self::lowStockHeader($pdf, $y);
            }

            if ($index % 2 === 1) {
                $pdf->fillRect($pdf->getLeft(), $y, $pdf->getContentWidth(), 18, self::LIGHT);
            }

            $left = $pdf->getLeft();
            $stock = (int)($item['stock'] ?? 0);

            $pdf->text(
                self::fitText($pdf, $item['product_name'] ?? '', 7.8, PdfDocument::FONT_BOLD, 140),
                $left + 8,
                $y + 5,
                7.8,
                PdfDocument::FONT_BOLD,
                self::INK
            );
            $pdf->text(
                self::fitText($pdf, ($item['size'] ?? '') . ' · ' . ($item['color'] ?? ''), 7.4, PdfDocument::FONT_REGULAR, 80),
                $left + 156,
                $y + 5,
                7.4,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                self::fitText($pdf, $item['sku'] ?? '', 6.8, PdfDocument::FONT_MONO, 84),
                $left + 244,
                $y + 5,
                6.8,
                PdfDocument::FONT_MONO,
                self::MUTED
            );
            $pdf->text(
                self::fitText($pdf, $item['seller_name'] ?? '', 7.2, PdfDocument::FONT_REGULAR, 88),
                $left + 336,
                $y + 5,
                7.2,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                '$' . number_format((float)($item['final_price'] ?? 0), 2),
                $left + 468,
                $y + 5,
                7.2,
                PdfDocument::FONT_REGULAR,
                self::INK,
                null,
                'right'
            );
            $pdf->text(
                $stock . ' pzs',
                $pdf->getRight() - 8,
                $y + 5,
                7.6,
                PdfDocument::FONT_BOLD,
                $stock <= 5 ? self::DANGER : self::AMBER_TEXT,
                null,
                'right'
            );

            $pdf->line($left, $y + 18, $pdf->getRight(), $y + 18, self::BORDER, 0.35);
            $y += 18;
        }

        return $y + 6;
    }

    private static function lowStockHeader(PdfDocument $pdf, float $y): float
    {
        $left = $pdf->getLeft();
        $pdf->fillRect($left, $y, $pdf->getContentWidth(), 18, self::DANGER_BG);

        $pdf->text('PRENDA', $left + 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::DANGER);
        $pdf->text('TALLA / COLOR', $left + 156, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::DANGER);
        $pdf->text('SKU', $left + 244, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::DANGER);
        $pdf->text('VENDEDOR', $left + 336, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::DANGER);
        $pdf->text('PRECIO', $left + 468, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::DANGER, null, 'right');
        $pdf->text('STOCK', $pdf->getRight() - 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::DANGER, null, 'right');

        return $y + 18;
    }

    private static function drawInventory(PdfDocument $pdf, array $data, float $y): float
    {
        $rows = $data['full_inventory'] ?? [];
        $title = 'Listado General de Existencias';
        $needed = 27 + 18 + (min(2, max(1, count($rows))) * 17);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $y = self::sectionTitle($pdf, $title, $y);
        $y = self::inventoryHeader($pdf, $y);

        if (empty($rows)) {
            $pdf->text('Sin existencias registradas.', $pdf->getLeft() + 8, $y + 5, 8, PdfDocument::FONT_ITALIC, self::STONE);
            return $y + 24;
        }

        $threshold = (int)($data['low_stock_threshold'] ?? 10);

        foreach ($rows as $index => $row) {
            if ($pdf->ensureSpace(17, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, $title . ' (continuación)', $y);
                $y = self::inventoryHeader($pdf, $y);
            }

            if ($index % 2 === 1) {
                $pdf->fillRect($pdf->getLeft(), $y, $pdf->getContentWidth(), 17, self::LIGHT);
            }

            $left = $pdf->getLeft();
            $stock = (int)($row['stock'] ?? 0);

            $pdf->text(
                self::fitText($pdf, $row['product_name'] ?? '', 7.2, PdfDocument::FONT_BOLD, 130),
                $left + 8,
                $y + 4.5,
                7.2,
                PdfDocument::FONT_BOLD,
                self::INK
            );
            $pdf->text(
                self::fitText($pdf, $row['category_name'] ?? '', 7, PdfDocument::FONT_REGULAR, 78),
                $left + 142,
                $y + 4.5,
                7,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                self::fitText($pdf, $row['size'] ?? '', 7, PdfDocument::FONT_REGULAR, 30),
                $left + 224,
                $y + 4.5,
                7,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                self::fitText($pdf, $row['color'] ?? '', 7, PdfDocument::FONT_REGULAR, 48),
                $left + 258,
                $y + 4.5,
                7,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                self::fitText($pdf, $row['sku'] ?? '', 6.6, PdfDocument::FONT_MONO, 56),
                $left + 310,
                $y + 4.5,
                6.6,
                PdfDocument::FONT_MONO,
                self::MUTED
            );
            $pdf->text(
                (string)$stock,
                $left + 400,
                $y + 4.5,
                7.4,
                PdfDocument::FONT_BOLD,
                $stock <= $threshold ? self::DANGER : self::INK,
                null,
                'right'
            );
            $pdf->text(
                '$' . number_format((float)($row['final_price'] ?? 0), 2),
                $left + 452,
                $y + 4.5,
                7.2,
                PdfDocument::FONT_REGULAR,
                self::STONE,
                null,
                'right'
            );
            $pdf->text(
                '$' . number_format((float)($row['total_value'] ?? 0), 2),
                $pdf->getRight() - 8,
                $y + 4.5,
                7.4,
                PdfDocument::FONT_BOLD,
                self::INK,
                null,
                'right'
            );

            $pdf->line($left, $y + 17, $pdf->getRight(), $y + 17, self::BORDER, 0.35);
            $y += 17;
        }

        return $y + 6;
    }

    private static function inventoryHeader(PdfDocument $pdf, float $y): float
    {
        $left = $pdf->getLeft();
        $pdf->fillRect($left, $y, $pdf->getContentWidth(), 18, self::LIGHT);

        $pdf->text('PRENDA', $left + 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('CATEGORÍA', $left + 142, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('TALLA', $left + 224, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('COLOR', $left + 258, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('SKU', $left + 310, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('STOCK', $left + 400, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');
        $pdf->text('PRECIO', $left + 452, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');
        $pdf->text('VALOR TOTAL', $pdf->getRight() - 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');

        return $y + 18;
    }

    private static function drawSignature(PdfDocument $pdf, array $data, float $y): float
    {
        if ($pdf->ensureSpace(86, $y)) {
            $y = self::continuation($pdf);
        }

        $left = $pdf->getLeft();
        $right = $pdf->getRight();
        $lineY = $y + 26;
        $signatureX = $right - 180.0;

        $pdf->line($left, $lineY, $left + 210, $lineY, self::BORDER, 0.7);
        $pdf->text('AURA Atelier E-Commerce System', $left, $lineY + 7, 8, PdfDocument::FONT_BOLD, self::INK);
        $pdf->text('Base de datos PostgreSQL · Arquitectura Hexagonal', $left, $lineY + 18, 6.6, PdfDocument::FONT_REGULAR, self::STONE);
        $pdf->text(
            'Documento confidencial para uso exclusivo de la gerencia general y el departamento de almacén.',
            $left,
            $lineY + 29,
            6.2,
            PdfDocument::FONT_REGULAR,
            self::MUTED,
            250
        );

        $pdf->line($signatureX, $lineY, $right, $lineY, self::BORDER, 0.7);
        $pdf->text(
            $data['user']['name'] ?? 'Administrador',
            $signatureX + 90,
            $lineY + 7,
            8,
            PdfDocument::FONT_BOLD,
            self::INK,
            null,
            'center'
        );
        $pdf->text(
            'Dirección General & Auditoría',
            $signatureX + 90,
            $lineY + 18,
            6.4,
            PdfDocument::FONT_REGULAR,
            self::MUTED,
            null,
            'center'
        );

        return $lineY + 44;
    }

    private static function sectionTitle(PdfDocument $pdf, string $title, float $y): float
    {
        $pdf->text($title, $pdf->getLeft(), $y, 9.6, PdfDocument::FONT_BOLD, self::INK);
        $pdf->line($pdf->getLeft(), $y + 17, $pdf->getRight(), $y + 17, self::BORDER, 0.5);

        return $y + 27;
    }

    private static function continuation(PdfDocument $pdf): float
    {
        $pdf->text(
            'AURA Atelier · Reporte de Inventario (continuación)',
            $pdf->getLeft(),
            $pdf->getY(),
            7,
            PdfDocument::FONT_BOLD,
            self::STONE
        );
        $pdf->line($pdf->getLeft(), $pdf->getY() + 12, $pdf->getRight(), $pdf->getY() + 12, self::BORDER, 0.5);

        return $pdf->getY() + 26;
    }

    private static function selectedCategoryName(array $data): string
    {
        $selectedId = $data['selected_category_id'] ?? null;

        if ($selectedId === null || $selectedId === '') {
            return 'Todas las categorías';
        }

        foreach ($data['categories'] ?? [] as $category) {
            if ((string)($category['id'] ?? '') === (string)$selectedId) {
                return (string)($category['name'] ?? 'Categoría seleccionada');
            }
        }

        return 'Categoría seleccionada';
    }

    private static function fitText(PdfDocument $pdf, ?string $text, float $size, string $font, float $maxWidth): string
    {
        $text = trim((string)$text);

        if ($pdf->measure($text, $size, $font) <= $maxWidth) {
            return $text;
        }

        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ellipsis = '...';

        while ($chars !== [] && $pdf->measure(implode('', $chars) . $ellipsis, $size, $font) > $maxWidth) {
            array_pop($chars);
        }

        return implode('', $chars) . $ellipsis;
    }

    private static function fitSize(PdfDocument $pdf, string $text, float $size, float $minimum, float $maxWidth, string $font): float
    {
        while ($size > $minimum && $pdf->measure($text, $size, $font) > $maxWidth) {
            $size -= 0.5;
        }

        return $size;
    }
}
