<?php

namespace App\Backend\Views\Reports;

use App\Backend\Views\Engine\PdfDocument;

/**
 * Construye el PDF del Reporte Ejecutivo de Ventas a partir de los datos
 * que entrega ReportController::getSalesReport().
 */
class SalesReportPdf
{
    private const INK = [0.12, 0.11, 0.10];
    private const STONE = [0.45, 0.43, 0.41];
    private const MUTED = [0.58, 0.55, 0.52];
    private const LIGHT = [0.968, 0.964, 0.956];
    private const TRACK = [0.905, 0.892, 0.875];
    private const BORDER = [0.865, 0.850, 0.825];
    private const ACCENT = [0.960, 0.650, 0.080];
    private const WHITE = [1.0, 1.0, 1.0];

    public static function render(array $data): string
    {
        $pdf = new PdfDocument(
            $data['title'] ?? 'Reporte Ejecutivo de Ventas | AURA Atelier',
            'AURA Atelier',
            'Reporte de ventas e ingresos'
        );

        $pdf->addPage();

        $y = self::drawHeader($pdf, $data);
        $y = self::drawMeta($pdf, $data, $y);
        $y = self::drawKpis($pdf, $data, $y);
        $y = self::drawCategories($pdf, $data, $y);
        $y = self::drawPayments($pdf, $data, $y);
        $y = self::drawTopProducts($pdf, $data, $y);
        $y = self::drawOrders($pdf, $data, $y);
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
            'Reporte Ejecutivo de Ventas & Facturación',
            $pdf->getLeft() + 86,
            43,
            14.5,
            PdfDocument::FONT_BOLD,
            self::WHITE
        );

        $pdf->text(
            'RPT-SLS-' . date('Ymd'),
            $right,
            27,
            7.8,
            PdfDocument::FONT_BOLD,
            self::ACCENT,
            null,
            'right'
        );

        $pdf->text(
            'Emitido: ' . ($data['generated_at'] ?? date('d/m/Y H:i:s')),
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

        $period = date('d/m/Y', strtotime($data['start_date'])) . ' al ' . date('d/m/Y', strtotime($data['end_date']));

        $items = [
            ['PERÍODO AUDITADO', $period],
            ['MONEDA', 'MXN · Pesos Mexicanos'],
            ['CATEGORÍA', self::selectedCategoryName($data)],
            ['AUDITOR', $data['user']['name'] ?? 'Auditoría Administrativa'],
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

        $cards = [
            [
                'label' => 'VENTAS NETAS TOTALES',
                'value' => '$' . number_format((float)($summary['total_sales'] ?? 0), 2),
                'sub' => 'MXN acumulado',
                'dark' => true,
            ],
            [
                'label' => 'ÓRDENES LIQUIDADAS',
                'value' => (string)(int)($summary['total_orders'] ?? 0),
                'sub' => '100% cobro confirmado',
                'dark' => false,
            ],
            [
                'label' => 'TICKET PROMEDIO',
                'value' => '$' . number_format((float)($summary['avg_ticket'] ?? 0), 2),
                'sub' => 'Por cliente / orden',
                'dark' => false,
            ],
            [
                'label' => 'PRENDAS ENTREGADAS',
                'value' => (string)(int)($summary['total_items_sold'] ?? 0),
                'sub' => 'Unidades físicas',
                'dark' => false,
            ],
        ];

        foreach ($cards as $index => $card) {
            $x = $left + ($index * ($cardWidth + $gap));
            $valueSize = self::fitSize($pdf, $card['value'], 15.5, 9.0, $cardWidth - 24, PdfDocument::FONT_BOLD);

            if ($card['dark']) {
                $pdf->fillRect($x, $y, $cardWidth, $cardHeight, self::INK);
            } else {
                $pdf->fillRect($x, $y, $cardWidth, $cardHeight, self::LIGHT);
                $pdf->strokeRect($x, $y, $cardWidth, $cardHeight, self::BORDER, 0.5);
            }

            $pdf->text(
                $card['label'],
                $x + 12,
                $y + 10,
                6.0,
                PdfDocument::FONT_BOLD,
                $card['dark'] ? self::ACCENT : self::MUTED,
                $cardWidth - 24
            );

            $pdf->text(
                $card['value'],
                $x + 12,
                $y + 24,
                $valueSize,
                PdfDocument::FONT_BOLD,
                $card['dark'] ? self::WHITE : self::INK,
                $cardWidth - 24
            );

            $pdf->text(
                $card['sub'],
                $x + 12,
                $y + 50,
                6.0,
                PdfDocument::FONT_REGULAR,
                $card['dark'] ? [0.65, 0.62, 0.58] : self::MUTED,
                $cardWidth - 24
            );
        }

        return $y + $cardHeight + 20;
    }

    private static function drawCategories(PdfDocument $pdf, array $data, float $y): float
    {
        $categories = $data['sales_by_category'] ?? [];
        $needed = 27 + (min(2, max(1, count($categories))) * 25);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $y = self::sectionTitle($pdf, 'Participación por Categoría', $y);
        $left = $pdf->getLeft();
        $width = $pdf->getContentWidth();
        $right = $pdf->getRight();

        if (empty($categories)) {
            $pdf->text('Sin ventas registradas en este período.', $left, $y, 8, PdfDocument::FONT_ITALIC, self::STONE);
            return $y + 26;
        }

        foreach ($categories as $category) {
            if ($pdf->ensureSpace(25, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, 'Participación por Categoría (continuación)', $y);
            }

            $name = self::fitText(
                $pdf,
                $category['category_name'] ?? 'Sin categoría',
                8.2,
                PdfDocument::FONT_REGULAR,
                $width - 150
            );
            $percentage = (float)($category['percentage'] ?? 0);
            $amount = '$' . number_format((float)($category['total_revenue'] ?? 0), 2) . '   (' . $percentage . '%)';

            $pdf->text($name, $left, $y, 8.2, PdfDocument::FONT_REGULAR, self::INK);
            $pdf->text($amount, $right, $y, 8.2, PdfDocument::FONT_BOLD, self::INK, null, 'right');

            $pdf->fillRect($left, $y + 13, $width, 5, self::TRACK);

            if ($percentage > 0) {
                $barWidth = max(2.5, $width * min(100, $percentage) / 100);
                $pdf->fillRect($left, $y + 13, $barWidth, 5, self::INK);
            }

            $y += 25;
        }

        return $y + 6;
    }

    private static function drawPayments(PdfDocument $pdf, array $data, float $y): float
    {
        $payments = $data['payment_methods'] ?? [];
        $needed = 27 + 18 + (min(2, max(1, count($payments))) * 18);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $title = 'Desglose por Método y Pasarela de Pago';
        $y = self::sectionTitle($pdf, $title, $y);
        $y = self::paymentHeader($pdf, $y);

        if (empty($payments)) {
            $pdf->text('Sin transacciones aprobadas en este período.', $pdf->getLeft() + 8, $y + 5, 8, PdfDocument::FONT_ITALIC, self::STONE);
            return $y + 24;
        }

        foreach ($payments as $index => $payment) {
            if ($pdf->ensureSpace(18, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, $title . ' (continuación)', $y);
                $y = self::paymentHeader($pdf, $y);
            }

            if ($index % 2 === 1) {
                $pdf->fillRect($pdf->getLeft(), $y, $pdf->getContentWidth(), 18, self::LIGHT);
            }

            $method = self::fitText(
                $pdf,
                $payment['payment_method'] ?? 'Tarjeta',
                8,
                PdfDocument::FONT_BOLD,
                230
            );

            $pdf->text($method, $pdf->getLeft() + 8, $y + 5, 8, PdfDocument::FONT_BOLD, self::INK);
            $pdf->text(
                (string)(int)($payment['transaction_count'] ?? 0),
                $pdf->getLeft() + 342,
                $y + 5,
                8,
                PdfDocument::FONT_REGULAR,
                self::STONE,
                null,
                'center'
            );
            $pdf->text(
                '$' . number_format((float)($payment['total_processed'] ?? 0), 2),
                $pdf->getRight() - 8,
                $y + 5,
                8,
                PdfDocument::FONT_BOLD,
                self::INK,
                null,
                'right'
            );

            $pdf->line($pdf->getLeft(), $y + 18, $pdf->getRight(), $y + 18, self::BORDER, 0.35);
            $y += 18;
        }

        return $y + 6;
    }

    private static function paymentHeader(PdfDocument $pdf, float $y): float
    {
        $pdf->fillRect($pdf->getLeft(), $y, $pdf->getContentWidth(), 18, self::LIGHT);
        $pdf->text('MÉTODO / PASARELA', $pdf->getLeft() + 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('TRANSACCIONES', $pdf->getLeft() + 342, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'center');
        $pdf->text('MONTO PROCESADO', $pdf->getRight() - 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');

        return $y + 18;
    }

    private static function drawTopProducts(PdfDocument $pdf, array $data, float $y): float
    {
        $products = $data['top_products'] ?? [];
        $needed = 27 + 18 + (min(2, max(1, count($products))) * 18);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $title = 'Top Prendas con Mayor Facturación';
        $y = self::sectionTitle($pdf, $title, $y);
        $y = self::productsHeader($pdf, $y);

        if (empty($products)) {
            $pdf->text('Sin prendas vendidas en este período.', $pdf->getLeft() + 8, $y + 5, 8, PdfDocument::FONT_ITALIC, self::STONE);
            return $y + 24;
        }

        foreach ($products as $index => $product) {
            if ($pdf->ensureSpace(19, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, $title . ' (continuación)', $y);
                $y = self::productsHeader($pdf, $y);
            }

            if ($index % 2 === 1) {
                $pdf->fillRect($pdf->getLeft(), $y, $pdf->getContentWidth(), 19, self::LIGHT);
            }

            $left = $pdf->getLeft();

            $pdf->text('#' . ($index + 1), $left + 8, $y + 5, 8, PdfDocument::FONT_BOLD, $index < 3 ? [0.72, 0.47, 0.05] : self::STONE);
            $pdf->text(
                self::fitText($pdf, $product['product_name'] ?? '', 8, PdfDocument::FONT_BOLD, 184),
                $left + 36,
                $y + 5,
                8,
                PdfDocument::FONT_BOLD,
                self::INK
            );
            $pdf->text(
                self::fitText($pdf, $product['category_name'] ?? '', 8, PdfDocument::FONT_REGULAR, 92),
                $left + 226,
                $y + 5,
                8,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                (string)(int)($product['total_quantity'] ?? 0) . ' uds',
                $left + 369,
                $y + 5,
                8,
                PdfDocument::FONT_REGULAR,
                self::INK,
                null,
                'right'
            );
            $pdf->text(
                '$' . number_format((float)($product['total_revenue'] ?? 0), 2),
                $pdf->getRight() - 8,
                $y + 5,
                8,
                PdfDocument::FONT_BOLD,
                self::INK,
                null,
                'right'
            );

            $pdf->line($left, $y + 19, $pdf->getRight(), $y + 19, self::BORDER, 0.35);
            $y += 19;
        }

        return $y + 6;
    }

    private static function productsHeader(PdfDocument $pdf, float $y): float
    {
        $left = $pdf->getLeft();
        $pdf->fillRect($left, $y, $pdf->getContentWidth(), 18, self::LIGHT);

        $pdf->text('RANK', $left + 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('PRENDA DE ROPA', $left + 36, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('CATEGORÍA', $left + 226, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('UNIDADES', $left + 369, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');
        $pdf->text('MONTO RECAUDADO', $pdf->getRight() - 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');

        return $y + 18;
    }

    private static function drawOrders(PdfDocument $pdf, array $data, float $y): float
    {
        $orders = $data['recent_orders'] ?? [];
        $needed = 27 + 18 + (min(2, max(1, count($orders))) * 18);

        if ($pdf->ensureSpace($needed, $y)) {
            $y = self::continuation($pdf);
        }

        $title = 'Detalle Cronológico de Pedidos';
        $y = self::sectionTitle($pdf, $title, $y);
        $y = self::ordersHeader($pdf, $y);

        if (empty($orders)) {
            $pdf->text('Sin pedidos pagados en este período.', $pdf->getLeft() + 8, $y + 5, 8, PdfDocument::FONT_ITALIC, self::STONE);
            return $y + 24;
        }

        foreach ($orders as $index => $order) {
            if ($pdf->ensureSpace(19, $y)) {
                $y = self::continuation($pdf);
                $y = self::sectionTitle($pdf, $title . ' (continuación)', $y);
                $y = self::ordersHeader($pdf, $y);
            }

            if ($index % 2 === 1) {
                $pdf->fillRect($pdf->getLeft(), $y, $pdf->getContentWidth(), 19, self::LIGHT);
            }

            $left = $pdf->getLeft();

            $pdf->text(
                self::fitText($pdf, $order['order_number'] ?? '', 7.2, PdfDocument::FONT_MONO, 98),
                $left + 8,
                $y + 5,
                7.2,
                PdfDocument::FONT_MONO,
                self::INK
            );
            $pdf->text(
                date('d/m/Y H:i', strtotime((string)($order['created_at'] ?? 'now'))),
                $left + 112,
                $y + 5,
                7.6,
                PdfDocument::FONT_REGULAR,
                self::STONE
            );
            $pdf->text(
                self::fitText($pdf, $order['customer_name'] ?? '', 7.8, PdfDocument::FONT_REGULAR, 190),
                $left + 208,
                $y + 5,
                7.8,
                PdfDocument::FONT_REGULAR,
                self::INK
            );
            $pdf->text(
                (string)(int)($order['item_count'] ?? 0) . ' pzs',
                $left + 430,
                $y + 5,
                7.6,
                PdfDocument::FONT_REGULAR,
                self::STONE,
                null,
                'right'
            );
            $pdf->text(
                '$' . number_format((float)($order['total_amount'] ?? 0), 2),
                $pdf->getRight() - 8,
                $y + 5,
                8,
                PdfDocument::FONT_BOLD,
                self::INK,
                null,
                'right'
            );

            $pdf->line($left, $y + 19, $pdf->getRight(), $y + 19, self::BORDER, 0.35);
            $y += 19;
        }

        return $y + 6;
    }

    private static function ordersHeader(PdfDocument $pdf, float $y): float
    {
        $left = $pdf->getLeft();
        $pdf->fillRect($left, $y, $pdf->getContentWidth(), 18, self::LIGHT);

        $pdf->text('PEDIDO', $left + 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('FECHA / HORA', $left + 112, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('CLIENTE', $left + 208, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED);
        $pdf->text('ÍTEMS', $left + 430, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');
        $pdf->text('TOTAL COBRADO', $pdf->getRight() - 8, $y + 5, 6.2, PdfDocument::FONT_BOLD, self::MUTED, null, 'right');

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
            'Documento confidencial para uso exclusivo de la gerencia general y el departamento fiscal.',
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
            'AURA Atelier · Reporte Ejecutivo de Ventas (continuación)',
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
