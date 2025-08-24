<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405); 
    exit; 
}

if (!isset($_POST['csrf']) || !isset($_SESSION['csrf']) || 
    !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    http_response_code(403);
    exit('CSRF token mismatch');
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
    $qty = filter_var($quantity, FILTER_VALIDATE_INT);
    return ($qty !== false && $qty >= 1 && $qty <= 50) ? $qty : null;
}

function validateDeliveryDate(string $date): ?string {
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        return null;
    }
    
    $today = new DateTime('today');
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
    
    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        throw new Exception('Invalid file type');
    }
    
    if ($mimeType === 'image/jpeg') {
        $image = imagecreatefromjpeg($fileInfo['tmp_name']);
        if (!$image) throw new Exception('Invalid JPEG');
        
        $filename = bin2hex(random_bytes(16)) . '.jpg';
        $uploadPath = __DIR__ . '/../uploads/' . $filename;
        
        imagejpeg($image, $uploadPath, 90);
        imagedestroy($image);
    } else {
        $image = imagecreatefrompng($fileInfo['tmp_name']);
        if (!$image) throw new Exception('Invalid PNG');
        
        $filename = bin2hex(random_bytes(16)) . '.png';
        $uploadPath = __DIR__ . '/../uploads/' . $filename;
        
        imagepng($image, $uploadPath, 9);
        imagedestroy($image);
    }
    
    return $filename;
}

function escapeHtml(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function escapeJs(mixed $value): string {
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

try {
    $name = validateAndNormalizeUTF8($_POST['name'] ?? '');
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    
    $email = validateEmail($_POST['email'] ?? '');
    if (!$email) {
        throw new Exception('Valid email is required');
    }
    
    $giftMessage = validateAndNormalizeUTF8($_POST['gift_message'] ?? '');
    if (empty($giftMessage)) {
        throw new Exception('gift message is required');
    }
    
    $quantity = validateQuantity($_POST['quantity'] ?? '');
    if ($quantity === null) {
        throw new Exception('Quantity must be between 1 and 50');
    }
    
    $deliveryDate = validateDeliveryDate($_POST['delivery_date'] ?? '');

    if (!$deliveryDate) {
        throw new Exception('Delivery date must be today or later and in YYYY-MM-DD format');
    }
    
    $photoFilename = null;
    if (isset($_FILES['photo'])) {
        $photoFilename = processPhoto($_FILES['photo']);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (name, email, gift_message, quantity, delivery_date, photo_filename, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $name,
        $email, 
        $giftMessage,
        $quantity,
        $deliveryDate,
        $photoFilename
    ]);
    
    $emailBody = "New Order Received:\n\n";
    $emailBody .= "name: " . $name . "\n";
    $emailBody .= "email: " . $email . "\n";
    $emailBody .= "message: " . $giftMessage . "\n";
    $emailBody .= "qty: " . $quantity . "\n";
    $emailBody .= "delivery date: " . $deliveryDate . "\n";
    if ($photoFilename) {
        $emailBody .= "Photo: " . $photoFilename . "\n";
    }
    
    $headers = "From: contact@bakemeawish.com\r\n";
    $headers .= "Reply-To: " .$email. "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail('emaildodiego@example.com', 'New Order Received', $emailBody,$headers);
    
} catch (Exception $e) {
    http_response_code(400);
    exit('Error: ' . $e->getMessage());
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Thanks, <?= escapeHtml($name) ?></h1>
    
    <div>
        <p>Your order details:</p>
        <p><strong>Email:</strong> <?= escapeHtml($email) ?></p>
        <p><strong>Quantity:</strong> <?= escapeHtml((string)$quantity) ?></p>
        <p><strong>Delivery Date:</strong> <?= escapeHtml($deliveryDate) ?></p>
        <p><strong>Message:</strong></p>
        <pre><?= escapeHtml($giftMessage) ?></pre>
    </div>
    
    <img alt="Sender" src="/avatar.png" data-sender="<?= escapeHtml($name) ?>">
    
    <script>
        const note = <?= escapeJs($giftMessage) ?>;
        console.log('Gift Message:', note);
        
        const orderData = {
            name: <?= escapeJs($name) ?>,
            email: <?= escapeJs($email) ?>,
            quantity: <?= escapeJs($quantity) ?>,
            deliveryDate: <?= escapeJs($deliveryDate) ?>,
            message: <?= escapeJs($giftMessage) ?>
        };
        
        console.log('Orderdata', orderData);
    </script>
</body>
</html>