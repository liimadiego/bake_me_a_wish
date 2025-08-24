<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../src/form_handler.php';
}

session_start();
$_SESSION['csrf'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Order</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="number"], input[type="date"], textarea, input[type="file"] {
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
        textarea { height: 100px; resize: vertical; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>Gift Order</h1>
    
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">

        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="gift_message">Gift Message:</label>
            <textarea id="gift_message" name="gift_message" required placeholder="Enter your gift message here..."></textarea>
        </div>
        
        <div class="form-group">
            <label for="quantity">Quantity (1-50):</label>
            <input type="number" id="quantity" name="quantity" min="1" max="50" required>
        </div>
        <div class="form-group">
            <label for="delivery_date">Delivery Date:</label>
            <input type="date" id="delivery_date" name="delivery_date" required>
        </div>
    
        <div class="form-group">
            <label for="photo">Photo (optional - JPG/PNG (max 2MB)):</label>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
        </div>
        
        <button type="submit">Place Order</button>
    </form>

    <script>
        // document.getElementById('delivery_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>