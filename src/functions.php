<?php
declare(strict_types=1);

function escapeHtml(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function escapeJs(mixed $value): string {
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function validateAndNormalizeUTF8(string $input): string {
    $clean = mb_convert_encoding($input, 'UTF-8', 'UTF-8');
    $clean = str_replace(["\r\n", "\r"], "\n", $clean);
    $clean = preg_replace('/[\x{2028}\x{2029}]/u', "\n", $clean);
    
    return trim($clean);
}

function validateEmail(string $email): ?string {
    $email = validateAndNormalizeUTF8($email);
    
    if (strpos($email, "\r") !== false || strpos($email, "\n") !== false) {
        return null;
    }
    
    return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
}

function validateQuantity(string $quantity): ?int {
    $qty = (int)$quantity;
    return ($qty >= 1 && $qty <= 50) ? $qty : null;
}

function validateDeliveryDate(string $date): ?string {
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        return null;
    }

    $businessTimezone = new DateTimeZone('America/Sao_Paulo'); //In my timezone for example
    $dateObj->setTimezone($businessTimezone);

    $today = new DateTime('today', $businessTimezone);
    return ($dateObj >= $today) ? $date : null;
}

function processPhoto(array $fileInfo): ?string {
    if ($fileInfo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($fileInfo['error'] !== UPLOAD_ERR_OK || $fileInfo['size'] > 2 * 1024 * 1024) {
        throw new Exception('File upload error or too large');
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($fileInfo['tmp_name']);

    $declaredMimeType = $fileInfo['type'];

    if ($declaredMimeType && $declaredMimeType !== $mimeType) {
        throw new Exception('Content-Type header does not match file content');
    }
    
    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        throw new Exception('Invalid file type');
    }
    
    $extension = ($mimeType === 'image/jpeg') ? '.jpg' : '.png';
    $filename = bin2hex(random_bytes(16)) . $extension;

    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create uploads directory');
        }
    }

    $uploadPath = $uploadDir . $filename;

    if ($mimeType === 'image/jpeg') {
        $img = imagecreatefromjpeg($fileInfo['tmp_name']);
        if (!$img) {
            throw new Exception('Failed to read JPEG image');
        }
        if (!imagejpeg($img, $uploadPath, 90)) {
            imagedestroy($img);
            throw new Exception('Failed to save processed image');
        }
        imagedestroy($img);
    } else {
        if (!move_uploaded_file($fileInfo['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to save uploaded file');
        }
    }
    
    return $filename;
}