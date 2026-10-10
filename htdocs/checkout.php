<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

if (!isset($_GET['medicine_id']) || empty($_GET['medicine_id'])) {
    header("Location: medicine.php");
    exit();
}

$medicine_id = intval($_GET['medicine_id']);

$stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
$stmt->bind_param("i", $medicine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: medicine.php");
    exit();
}

$medicine = $result->fetch_assoc();
$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $quantity = intval($_POST['quantity'] ?? 1);
    $payment_method = $_POST['payment_method'] ?? 'COD';

    if ($quantity <= 0) {
        $error_msg = "Please enter a valid quantity of at least 1.";
    } else {
        $medicine_name = $medicine['name'];
        $store_name = $medicine['store_location'];

        $stmt = $conn->prepare("
            INSERT INTO medicine_orders 
            (user_id, medicine_name, quantity, store_name, payment_method, order_status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->bind_param("isiss", $user_id, $medicine_name, $quantity, $store_name, $payment_method);

        if ($stmt->execute()) {
            $success_msg = "Order placed successfully! Redirecting to dashboard...";
            echo "<script>setTimeout(() => { window.location='dashboard.php'; }, 1600);</script>";
        } else {
            $error_msg = "Error placing order: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Checkout: <?php echo htmlspecialchars($medicine['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-box {
            max-width: 480px;
            margin: 40px auto;
        }

        .summary-banner {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 24px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .summary-row strong {
            color: #fff;
        }

        .total-price {
            font-size: 24px;
            font-weight: 800;
            color: #34d399;
            font-family: var(--font-heading);
        }
    </style>
    <script>
        const unitPrice = <?php echo (float)$medicine['price']; ?>;
        function updateTotal() {
            const qty = parseInt(document.getElementById("qty-input").value) || 1;
            const total = (qty * unitPrice).toFixed(2);
            document.getElementById("total-display").innerText = "₹" + total;
        }
    </script>
</head>
<body>

<div class="page-backdrop">

    <!-- NAVBAR -->
    <header class="med-navbar">
        <a href="index.php" class="brand-wrapper">
            <div class="brand-logo-icon">🩺</div>
            <div class="brand-name">
                MediVerse
                <span class="brand-badge">CHECKOUT</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="medicine.php" class="btn btn-secondary btn-sm">⬅ Back to Pharmacy</a>
        </nav>
    </header>

    <main class="container">

        <div class="glass-panel checkout-box">

            <div style="text-align: center; margin-bottom: 24px;">
                <span class="badge badge-success" style="margin-bottom: 8px;">Order Confirmation</span>
                <h2><?php echo htmlspecialchars($medicine['name']); ?></h2>
                <p style="color: var(--text-muted); font-size: 13px;">📍 Fulfilled by <?php echo htmlspecialchars($medicine['store_location']); ?></p>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px;">
                    ✅ <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 12px; border-radius: var(--radius-md); margin-bottom: 20px;">
                    ⚠️ <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="summary-banner">
                    <div class="summary-row">
                        <span>Unit Price</span>
                        <strong>₹<?php echo number_format($medicine['price'], 2); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Estimated Total</span>
                        <span id="total-display" class="total-price">₹<?php echo number_format($medicine['price'], 2); ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" id="qty-input" name="quantity" value="1" min="1" max="99" class="form-control" oninput="updateTotal()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="COD">Cash on Delivery (COD)</option>
                        <option value="Online">Online UPI / Card Payment</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success btn-lg btn-full" style="margin-top: 15px;">
                    Confirm & Place Order 🛍️
                </button>

            </form>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; Secure Checkout & Encrypted Transactions
    </footer>

</div>

</body>
</html>