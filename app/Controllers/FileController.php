<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Auth;
use MyFolio\Core\Database;
use MyFolio\Models\PaFile;

final class FileController
{
    public function view(string $id): never
    {
        if (!ctype_digit($id)) {
            http_response_code(404);
            exit('File not found');
        }

        $file = PaFile::findAccessible((int) $id, !Auth::check());
        if ($file === null) {
            http_response_code(404);
            exit('File not found');
        }

        $storageRoot = realpath(__DIR__ . '/../../storage/uploads');
        $filePath = $storageRoot === false ? false : realpath($storageRoot . '/' . ltrim($file['storage_path'], '/'));
        if ($filePath === false || !str_starts_with($filePath, $storageRoot . DIRECTORY_SEPARATOR) || !is_readable($filePath)) {
            http_response_code(404);
            exit('File not found');
        }

        $log = Database::connection()->prepare('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (:user_id, :action, :entity_type, :entity_id, :ip_address)');
        $log->execute([
            'user_id' => Auth::user()['id'] ?? null,
            'action' => 'file.view',
            'entity_type' => 'pa_file',
            'entity_id' => $file['id'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Length: ' . filesize($filePath));
        $downloadName = preg_replace('/[\r\n"\\]/', '_', basename((string) $file['original_name'])) ?: 'file';
        header('Content-Disposition: inline; filename="' . $downloadName . '"');
        readfile($filePath);
        exit;
    }
}