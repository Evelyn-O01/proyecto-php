<?php

namespace App\Backend\Domain\Ports;

interface FileStoragePort
{
    /**
     * @param array $file $_FILES array item
     * @param string $folder subfolder inside uploads
     * @return string public accessible URL/path
     */
    public function upload(array $file, string $folder = 'products'): string;

    public function delete(string $path): bool;
}
