<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405); 
    exit; 
}

if (!isset($_POST['csrf']) || !isset($_SESSION['csrf']) || 
    !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    http_response_code(403);
    exit('CSRF token mismatch');
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

    $to_ops = 'ops@bakemeawish.com';
    $subject = 'New Form Submission - ' . date('Y-m-d H:i:s');
    
    $headers = "From: example@bakemeawish.com\r\n";
    $headers .= "Reply-To: " .$to_ops. "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    //* ------------------------------ :D *//
    //Here I would send the email, but it is necessary to configure SMTP
    //* ------------------------------ :D *//

    $_SESSION['order_data'] = [
        'name' => $name,
        'email' => $email,
        'quantity' => $quantity,
        'delivery_date' => $deliveryDate,
        'gift_message' => $giftMessage,
        'photo_filename' => $photoFilename
    ];
    
    header('Location: /order_confirmation.php');
    exit;
    
} catch (Exception $e) {
    http_response_code(400);
    exit('Error: ' . $e->getMessage());
}
?>