<?php
session_start();
require __DIR__ . '/../src/functions.php';

if (!isset($_SESSION['order_data'])) {
    header('Location: index.php');
    exit;
}

$orderData = $_SESSION['order_data'];
$name = $orderData['name'] ?? '';
$email = $orderData['email'] ?? '';
$quantity = $orderData['quantity'] ?? '';
$deliveryDate = $orderData['delivery_date'] ?? '';
$giftMessage = $orderData['gift_message'] ?? '';
$photoFilename = $orderData['photo_filename'] ?? null;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        h1 { color: #007cba; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; white-space: pre-wrap; }
        .order-details { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Thanks, <?= escapeHtml($name) ?></h1>
    
    <div class="order-details">
        <p>Your order details:</p>
        <p><strong>Email:</strong> <?= escapeHtml($email) ?></p>
        <p><strong>Quantity:</strong> <?= escapeHtml((string)$quantity) ?></p>
        <p><strong>Delivery Date:</strong> <?= escapeHtml($deliveryDate) ?></p>
        <p><strong>Message:</strong></p>
        <pre><?= escapeHtml($giftMessage) ?></pre>
        
        <?php if ($photoFilename): ?>
        <p><strong>Photo:</strong> Uploaded successfully</p>
        <?php endif; ?>
    </div>
    
    <!-- <img alt="Sender" src="/avatar.png" data-sender="<?= escapeHtml($name) ?>"> -->
    
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
