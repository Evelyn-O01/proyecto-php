<?php

namespace App\Infrastructure\Storage;

use App\Domain\Ports\FileStoragePort;
use RuntimeException;

class LocalStorageAdapter implements FileStoragePort
{
    private string $uploadBasePath;

    public function __construct(?string $basePath = null)
    {
        $this->uploadBasePath = $basePath ?: dirname(__DIR__, 3) . '/public/uploads';
    }

    public function upload(array $file, string $folder = 'products'): string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException("El archivo no es una carga válida.");
        }

        $targetDir = $this->uploadBasePath . '/' . trim($folder, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
        if (!in_array($extension, $allowed)) {
            throw new RuntimeException("Formato de imagen no permitido ({$extension}). Use JPG, PNG, WEBP o AVIF.");
        }

        // Limitar tamaño (máximo 8 MB)
        if ($file['size'] > 8 * 1024 * 1024) {
            throw new RuntimeException("El archivo supera el tamaño máximo de 8 MB.");
        }

        $filename = uniqid('prod_', true) . '.' . $extension;
        $destination = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException("No se pudo mover el archivo al directorio de destino.");
        }

        return '/uploads/' . trim($folder, '/') . '/' . $filename;
    }

    public function delete(string $path): bool
    {
        $cleanPath = ltrim(parse_url($path, PHP_URL_PATH), '/');
        $fullPath = dirname($this->uploadBasePath) . '/' . $cleanPath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
