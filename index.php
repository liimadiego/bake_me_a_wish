<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gift Order Form</title>
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
        .test-cases { margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 4px; }
        .test-case { margin: 10px 0; padding: 10px; background: white; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Gift Order Form</h1>
    
    <form action="form_handler.php" method="POST" enctype="multipart/form-data">
        <!-- CSRF Token would be generated server-side -->
        <input type="hidden" name="csrf" value="<?php echo bin2hex(random_bytes(32)); ?>">
        
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
            <label for="photo">Photo (Optional - JPG/PNG, max 2MB):</label>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
        </div>
        
        <button type="submit">Place Order</button>
    </form>
    
    <!-- <div class="test-cases">
        <h2>Test Cases for Edge Cases:</h2>
        
        <div class="test-case">
            <strong>Name Tests:</strong>
            <ul>
                <li>Max 🚀 (emoji support)</li>
                <li>Alice&lt;script&gt;alert(1)&lt;/script&gt; (XSS attempt)</li>
                <li>Eve   O'Connor (line separators)</li>
            </ul>
        </div>
        
        <div class="test-case">
            <strong>Email Tests:</strong>
            <ul>
                <li>ops@example.com (valid)</li>
                <li>bad@example.com\r\nBcc: attacker@evil.test (header injection)</li>
            </ul>
        </div>
        
        <div class="test-case">
            <strong>Message Tests:</strong>
            <ul>
                <li>&lt;svg onload=alert(1)&gt; (XSS attempt)</li>
                <li>Mixed newlines: Line1\r\nLine2\rLine3\n</li>
                <li>‏abc‎ def (bidirectional text)</li>
            </ul>
        </div>
        
        <div class="test-case">
            <strong>Quantity Tests:</strong>
            <ul>
                <li>0005 (should become 5)</li>
                <li>51 (should be rejected)</li>
            </ul>
        </div>
        
        <div class="test-case">
            <strong>Date Tests:</strong>
            <ul>
                <li>1999-01-01 (past date - reject)</li>
                <li>2030-02-30 (invalid date - reject)</li>
            </ul>
        </div>
    </div> -->

    <script>
        // Set minimum date to today
        document.getElementById('delivery_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>