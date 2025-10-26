<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileUploader
{
    private string $uploadDir;

    private int $maxSize;

    private array $whitelist;

    private array $blacklist;

    private string $naming;

    private MimeTypeChecker $mimeChecker;

    public function __construct(
        array $uploadConfig,           // 上传配置
        MimeTypeChecker $mimeChecker  // MIME 检查器
    ) {
        $this->mimeChecker = $mimeChecker;

        $this->maxSize   = $uploadConfig['max_size'] ?? 5 * 1024 * 1024;
        $this->whitelist = array_map('strtolower', $uploadConfig['whitelist_extensions'] ?? []);
        $this->blacklist = array_map('strtolower', $uploadConfig['blacklist_extensions'] ?? []);
        $this->naming    = $uploadConfig['naming'] ?? 'uuid';

        if (! in_array($this->naming, ['original', 'uuid', 'datetime', 'md5'])) {
            throw new \InvalidArgumentException("Invalid naming strategy: {$this->naming}");
        }

        $uploadDir       = $uploadConfig['upload_dir'] ?? throw new \InvalidArgumentException('Missing upload_dir');
        $this->uploadDir = str_replace('%kernel.project_dir%', $this->getProjectDir(), $uploadDir);
		

        if (! is_dir($this->uploadDir) && ! mkdir($this->uploadDir, 0755, true)) {
            throw new \RuntimeException("Failed to create upload directory: {$this->uploadDir}");
        }

        if (! is_writable($this->uploadDir)) {
            throw new \RuntimeException("Upload directory is not writable: {$this->uploadDir}");
        }
    }

    public function upload(Request $request, string $formName = 'file'): array
    {
        $files = $request->files->get($formName);
        if (! $files) {
            throw new \InvalidArgumentException("No file found under key '{$formName}'");
        }

        $fileList = is_array($files) ? $files : [$files];
        $results  = [];

        foreach ($fileList as $file) {
            if (! $file instanceof UploadedFile) {
                throw new \InvalidArgumentException('Invalid uploaded file.');
            }
            $results[] = $this->handleFile($file);
        }

        return $results;
    }

    private function handleFile(UploadedFile $file): array
    {
        // 1. 检查上传错误
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \RuntimeException($this->getUploadErrorMessage($file->getError()));
        }

        // 2. 检查文件大小
        if ($file->getSize() > $this->maxSize) {
            throw new \RuntimeException("File size exceeds limit ({$this->maxSize} bytes).");
        }

        // 3. 获取扩展名（优先原始扩展名，其次从 MIME 推断）
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        if (! $extension) {
            $detectedMime       = $file->getMimeType();
            $inferredExtensions = $this->mimeChecker->getExtensionsByMime($detectedMime);
            $extension          = $inferredExtensions[0] ?? '';
        }
        if (! $extension) {
            throw new \RuntimeException('Cannot determine file extension and no valid MIME mapping found.');
        }

        // 4. 黑名单检查
        if (in_array($extension, $this->blacklist)) {
            throw new \RuntimeException("File extension '{$extension}' is blacklisted.");
        }

        // 5. 白名单检查
        if (! empty($this->whitelist) && ! in_array($extension, $this->whitelist)) {
            throw new \RuntimeException("File extension '{$extension}' is not allowed.");
        }

        // 6. MIME 类型严格校验（使用 fileinfo）
        $expectedMime = $this->mimeChecker->getMimeByExtension($extension);
        if (! $expectedMime || $expectedMime === 'application/octet-stream') {
            throw new \RuntimeException("No valid MIME type defined for extension: {$extension}");
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            throw new \RuntimeException('Temporary uploaded file not found.');
        }

        $detectedMime = $finfo->file($realPath);
        if (! $detectedMime) {
            throw new \RuntimeException('Unable to detect MIME type of uploaded file.');
        }

        if ($detectedMime !== $expectedMime) {
            throw new \RuntimeException("Suspicious MIME type detected: {$detectedMime}, expected: {$expectedMime}");
        }

        // 7. 生成日期子目录（格式：Y-m-d）
        $datePath     = date('Y-m-d'); // 如 2025-10-12
        $targetSubDir = $this->uploadDir . '/' . $datePath;

        if (! is_dir($targetSubDir) && ! mkdir($targetSubDir, 0755, true)) {
            throw new \RuntimeException("Failed to create upload subdirectory: {$targetSubDir}");
        }

        // 8. 生成安全文件名并构建目标路径
        $safeFilename = $this->generateSafeFilename($file->getClientOriginalName(), $extension);
        $targetPath   = $targetSubDir . '/' . $safeFilename;
		
		
        $size =$file->getSize();
		

        // 9. 移动文件（必须在 getRealPath() 之后、文件消失前完成所有读取）
        try {
			if (defined('WORKERMAN_VERSION')) {
				// Workerman 模式：手动移动文件
				$realPath = $file->getRealPath();
				if (!@rename($realPath, $targetPath)) {
					if (!@copy($realPath, $targetPath)) {
						throw new \RuntimeException("Failed to move uploaded file manually (Workerman mode).");
					}
					@unlink($realPath);
				}
			} else {
				// PHP-FPM 模式：使用 Symfony 内置方法
				$file->move($targetSubDir, $safeFilename);
			}
        } catch (\Exception $e) {
            throw new \RuntimeException('Error: Failed to move uploaded file: ' . $e->getMessage());
        }

        // 10. 计算 MD5 哈希（操作已保存的文件）
        if (! is_file($targetPath)) {
            throw new \RuntimeException("Uploaded file not found after move: {$targetPath}");
        }
        $md5Hash = md5_file($targetPath);

        // 11. 构造 Web 路径（相对于 public/）
        $webPath = '/uploads/' . $datePath . '/' . $safeFilename;
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $fullUrl = rtrim($baseUrl, '/') . $webPath;

        // 12. 返回结果
        return [
            'original_name' => $file->getClientOriginalName(),
            'size'          => $size,
            'extension'     => $extension,
            'mime'          => $detectedMime,
            'uploaded_at'   => date('Y-m-d H:i:s'),
            'saved_name'    => $safeFilename,
            'path'          => $webPath,      // ✅ 如 /uploads/2025-10-12/xxx.jpg
            'url'           => $fullUrl,
            'hash'          => $md5Hash,      // ✅ MD5 哈希
        ];
    }

    private function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'The file is larger than upload_max_filesize.',
            UPLOAD_ERR_FORM_SIZE  => 'The file is larger than MAX_FILE_SIZE.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
            default               => 'Unknown upload error.',
        };
    }

    private function generateSafeFilename(string $originalName, string $extension): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        return match ($this->naming) {
            'uuid'     => generateUuid() . '.' . $extension, // Uuid::v4() . '.' . $extension,
            'datetime' => (new \DateTime())->format('Ymd_His_u') . '.' . $extension,
            'md5'      => md5_file($file->getRealPath()) . '.' . $extension,
            'original' => $name . '.' . $extension,
            default    => $name . '.' . $extension,
        };
    }

    /* 遗弃 */
    private function getExtensionFromMime(?string $mime): string
    {
        return match ($mime) {
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'application/pdf' => 'pdf',
            'text/plain'      => 'txt',
            default           => '',
        };
    }

    private function getProjectDir(): string
    {
        return \dirname(__DIR__, 3); // src -> src/../ -> project root
    }
}
