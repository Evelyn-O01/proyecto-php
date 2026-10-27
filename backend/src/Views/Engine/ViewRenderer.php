<?php

namespace App\Backend\Views\Engine;

class ViewRenderer
{
    public static function render(string $template, array $data = []): string
    {
        extract($data);
        $file = dirname(__DIR__) . '/' . ltrim($template, '/') . '.php';

        if (!file_exists($file)) {
            return "Error: Plantilla de vista no encontrada en {$file}";
        }

        ob_start();
        require $file;
        return ob_get_clean();
    }

    public static function exportCsv(array $headers, array $rows, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM para Excel
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
}
