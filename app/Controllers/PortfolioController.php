<?php

namespace MyFolio\Controllers;

use MyFolio\Core\Auth;
use MyFolio\Core\Controller;
use MyFolio\Core\RBAC;
use MyFolio\Models\PaCategory;
use MyFolio\Models\PaFile;
use MyFolio\Models\PaItem;

final class PortfolioController extends Controller
{
    public function index(): void
    {
        $this->view('portfolio/show', [
            'title' => 'MyFolio',
            'categories' => PaCategory::ensureDefaults(),
            'items' => PaItem::published(),
        ]);
    }

    public function create(): void
    {
        RBAC::require('admin');
        $this->view('portfolio/form', ['title' => 'เพิ่มผลงาน', 'categories' => PaCategory::ensureDefaults(), 'item' => null]);
    }

    public function edit(string $id): void
    {
        $item = $this->managedItem($id);
        $this->view('portfolio/form', ['title' => 'แก้ไขผลงาน', 'categories' => PaCategory::ensureDefaults(), 'item' => $item]);
    }

    public function update(string $id): never
    {
        $item = $this->managedItem($id);
        $this->requirePostCsrf();
        $data = $this->validatedData();
        PaItem::update((int) $item['id'], $data);
        $this->attachUploads((int) $item['id']);
        $this->redirect('/dashboard');
    }

    public function delete(string $id): never
    {
        $item = $this->managedItem($id);
        $this->requirePostCsrf();
        $files = PaFile::forItem((int) $item['id']);
        PaItem::delete((int) $item['id']);
        $storageRoot = dirname(__DIR__, 2) . '/storage/uploads';
        foreach ($files as $file) {
            $path = realpath($storageRoot . '/' . ltrim($file['storage_path'], '/'));
            if ($path !== false && str_starts_with($path, realpath($storageRoot) . DIRECTORY_SEPARATOR)) {
                @unlink($path);
            }
        }
        $this->redirect('/dashboard');
    }

    public function store(): never
    {
        RBAC::require('admin');
        $this->requirePostCsrf();
        $data = $this->validatedData();
        $uploads = $_FILES['evidence']['name'] ?? [];
        $uploadErrors = $_FILES['evidence']['error'] ?? [];
        $uploadTmpNames = $_FILES['evidence']['tmp_name'] ?? [];
        $uploadSizes = $_FILES['evidence']['size'] ?? [];
        $uploadTypes = $_FILES['evidence']['type'] ?? [];

        if (!is_array($uploads) || count(array_filter($uploads, static fn (string $name): bool => $name !== '')) > 10) {
            http_response_code(422);
            exit('กรุณากรอกข้อมูลให้ถูกต้อง และแนบไฟล์ไม่เกิน 10 ไฟล์');
        }

        $database = \MyFolio\Core\Database::connection();
        $storageRoot = dirname(__DIR__, 2) . '/storage/uploads';
        if (!is_dir($storageRoot) && !mkdir($storageRoot, 0750, true) && !is_dir($storageRoot)) {
            http_response_code(500);
            exit('ไม่สามารถเตรียมพื้นที่จัดเก็บไฟล์ได้');
        }

        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        $movedFiles = [];
        try {
            $database->beginTransaction();
            $itemId = PaItem::create([
                ...$data,
                'created_by' => Auth::user()['id'] ?? null,
            ]);

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            foreach ($uploads as $index => $originalName) {
                if ($originalName === '') {
                    continue;
                }
                if (($uploadErrors[$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($uploadSizes[$index] ?? 0) > 20 * 1024 * 1024) {
                    throw new \RuntimeException('ไฟล์มีขนาดเกิน 20 MB หรืออัปโหลดไม่สำเร็จ');
                }
                $mimeType = $finfo->file($uploadTmpNames[$index]) ?: ($uploadTypes[$index] ?? '');
                if (!in_array($mimeType, $allowedTypes, true)) {
                    throw new \RuntimeException('อนุญาตเฉพาะ PDF, JPG, PNG และ WebP');
                }
                $storageName = bin2hex(random_bytes(16)) . '.' . (str_contains($mimeType, 'pdf') ? 'pdf' : (str_contains($mimeType, 'jpeg') ? 'jpg' : (str_contains($mimeType, 'png') ? 'png' : 'webp')));
                $relativePath = $storageName;
                $targetPath = $storageRoot . '/' . $storageName;
                if (!move_uploaded_file($uploadTmpNames[$index], $targetPath)) {
                    throw new \RuntimeException('ไม่สามารถย้ายไฟล์เข้าสู่พื้นที่จัดเก็บได้');
                }
                $movedFiles[] = $targetPath;
                PaFile::create([
                    'pa_item_id' => $itemId,
                    'original_name' => mb_substr(basename($originalName), 0, 255),
                    'storage_path' => $relativePath,
                    'mime_type' => $mimeType,
                    'file_size' => (int) $uploadSizes[$index],
                    'page_count' => null,
                    'uploaded_by' => Auth::user()['id'] ?? null,
                ]);
            }
            $database->commit();
        } catch (\Throwable $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            foreach ($movedFiles as $movedFile) {
                @unlink($movedFile);
            }
            http_response_code(422);
            exit($exception->getMessage());
        }
        $this->redirect('/dashboard');
    }

    private function managedItem(string $id): array
    {
        if (!ctype_digit($id)) {
            http_response_code(404);
            exit('ไม่พบผลงาน');
        }
        RBAC::require('admin', 'committee', 'public');
        $item = PaItem::find((int) $id);
        $user = Auth::user();
        if ($item === null || (($user['role'] ?? '') !== 'admin' && (int) ($item['created_by'] ?? 0) !== (int) ($user['id'] ?? 0))) {
            http_response_code(403);
            exit('คุณไม่มีสิทธิ์แก้ไขผลงานนี้');
        }
        return $item;
    }

    private function validatedData(): array
    {
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $assessmentPart = (string) ($_POST['assessment_part'] ?? '');
        $evaluationYear = filter_input(INPUT_POST, 'evaluation_year', FILTER_VALIDATE_INT);
        $evaluationRound = (string) ($_POST['evaluation_round'] ?? '');
        $description = trim((string) ($_POST['description'] ?? ''));
        $githubUrl = trim((string) ($_POST['github_url'] ?? ''));
        $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
        $category = $categoryId ? PaCategory::find($categoryId) : null;
        if (!$categoryId || !$category || $category['assessment_part'] !== $assessmentPart || !$evaluationYear || $evaluationYear < 2500 || $evaluationYear > 2700 || !in_array($assessmentPart, ['part_1', 'part_2'], true) || !in_array($evaluationRound, ['salary_march', 'vpa_september'], true) || ($githubUrl !== '' && !filter_var($githubUrl, FILTER_VALIDATE_URL))) {
            http_response_code(422);
            exit('กรุณากรอกข้อมูลให้ถูกต้อง');
        }
        return ['category_id' => $categoryId, 'evaluation_year' => $evaluationYear, 'evaluation_round' => $evaluationRound, 'title' => $category['name'], 'description' => $description, 'github_url' => $githubUrl !== '' ? $githubUrl : null, 'status' => $status];
    }

    private function attachUploads(int $itemId): void
    {
        $names = $_FILES['evidence']['name'] ?? [];
        if (!is_array($names)) {
            return;
        }
        if (count(array_filter($names, static fn (string $name): bool => $name !== '')) === 0) {
            return;
        }
        if (count(array_filter($names, static fn (string $name): bool => $name !== '')) > 10) {
            http_response_code(422);
            exit('แนบไฟล์ได้ไม่เกิน 10 ไฟล์ต่อครั้ง');
        }

        $storageRoot = dirname(__DIR__, 2) . '/storage/uploads';
        if (!is_dir($storageRoot) && !mkdir($storageRoot, 0750, true) && !is_dir($storageRoot)) {
            http_response_code(500);
            exit('ไม่สามารถเตรียมพื้นที่จัดเก็บไฟล์ได้');
        }
        $errors = $_FILES['evidence']['error'] ?? [];
        $tmpNames = $_FILES['evidence']['tmp_name'] ?? [];
        $sizes = $_FILES['evidence']['size'] ?? [];
        $types = $_FILES['evidence']['type'] ?? [];
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        $movedFiles = [];
        $database = \MyFolio\Core\Database::connection();
        try {
            $database->beginTransaction();
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            foreach ($names as $index => $originalName) {
                if ($originalName === '') {
                    continue;
                }
                if (($errors[$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($sizes[$index] ?? 0) > 20 * 1024 * 1024) {
                    throw new \RuntimeException('ไฟล์มีขนาดเกิน 20 MB หรืออัปโหลดไม่สำเร็จ');
                }
                $mimeType = $finfo->file($tmpNames[$index]) ?: ($types[$index] ?? '');
                if (!in_array($mimeType, $allowedTypes, true)) {
                    throw new \RuntimeException('อนุญาตเฉพาะ PDF, JPG, PNG และ WebP');
                }
                $extension = str_contains($mimeType, 'pdf') ? 'pdf' : (str_contains($mimeType, 'jpeg') ? 'jpg' : (str_contains($mimeType, 'png') ? 'png' : 'webp'));
                $storageName = bin2hex(random_bytes(16)) . '.' . $extension;
                $targetPath = $storageRoot . '/' . $storageName;
                if (!move_uploaded_file($tmpNames[$index], $targetPath)) {
                    throw new \RuntimeException('ไม่สามารถย้ายไฟล์เข้าสู่พื้นที่จัดเก็บได้');
                }
                $movedFiles[] = $targetPath;
                PaFile::create([
                    'pa_item_id' => $itemId,
                    'original_name' => mb_substr(basename($originalName), 0, 255),
                    'storage_path' => $storageName,
                    'mime_type' => $mimeType,
                    'file_size' => (int) $sizes[$index],
                    'page_count' => null,
                    'uploaded_by' => Auth::user()['id'] ?? null,
                ]);
            }
            $database->commit();
        } catch (\Throwable $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            foreach ($movedFiles as $movedFile) {
                @unlink($movedFile);
            }
            http_response_code(422);
            exit($exception->getMessage());
        }
    }
}