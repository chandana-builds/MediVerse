<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

if (!isset($_GET['medicine_id']) || !is_numeric($_GET['medicine_id'])) {
    header("Location: medicine.php");
    exit();
}

$medicine_id = intval($_GET['medicine_id']);

$stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
$stmt->bind_param("i", $medicine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: medicine.php");
    exit();
}

$medicine = $result->fetch_assoc();
$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $medicine_name = $medicine['name'];
    $store_name = $medicine['store_location'];
    $quantity = intval($_POST['quantity'] ?? 1);
    $payment_method = $_POST['payment_method'] ?? 'COD';

    if (!isset($_FILES['prescription']) || $_FILES['prescription']['error'] !== 0) {
        $error_msg = "Please upload a valid prescription file.";
    } else {
        $file = $_FILES['prescription'];
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file['type'], $allowed_types)) {
            $error_msg = "Only PDF, JPG, and PNG documents are allowed.";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error_msg = "File too large (Max allowed is 5MB).";
        } else {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }

            $new_file_name = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
            $upload_path = "uploads/" . $new_file_name;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("
                    INSERT INTO medicine_orders 
                    (user_id, medicine_name, quantity, store_name, payment_method, prescription_required, prescription_file, order_status)
                    VALUES (?, ?, ?, ?, ?, 1, ?, 'Pending')
                ");

                $stmt->bind_param(
                    "isisss",
                    $user_id,
                    $medicine_name,
                    $quantity,
                    $store_name,
                    $payment_method,
                    $upload_path
                );

                if ($stmt->execute()) {
                    $success_msg = "Prescription verified & order placed! Redirecting...";
                    echo "<script>setTimeout(() => { window.location='dashboard.php'; }, 1600);</script>";
                } else {
                    $error_msg = "Database Error: " . $stmt->error;
                }
            } else {
                $error_msg = "Server failed to save uploaded file.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Upload Prescription</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .rx-card {
            max-width: 500px;
            margin: 40px auto;
        }

        .rx-info-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: var(--radius-md);
            padding: 18px;
            margin-bottom: 22px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="page-backdrop">

    <!-- NAVBAR -->
    <header class="med-navbar">
        <a href="index.php" class="brand-wrapper">
            <div class="brand-logo-icon">🩺</div>
            <div class="brand-name">
                MediVerse
                <span class="brand-badge">RX VERIFICATION</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="medicine.php" class="btn btn-secondary btn-sm">⬅ Back to Pharmacy</a>
        </nav>
    </header>

    <main class="container">

        <div class="glass-panel rx-card">

            <div style="text-align: center; margin-bottom: 20px;">
                <span class="badge badge-warning" style="margin-bottom: 8px;">Prescription Required</span>
                <h2>Upload Prescription</h2>
                <p style="color: var(--text-muted); font-size: 14px;"><strong><?php echo htmlspecialchars($medicine['name']); ?></strong> (₹<?php echo number_format($medicine['price'], 2); ?>)</p>
            </div>

            <div class="rx-info-box">
                <div style="font-weight: 600; color: #fbbf24; margin-bottom: 4px;">⚠️ Regulatory Compliance</div>
                <div style="font-size: 13px; color: var(--text-muted);">
                    This medication requires a valid prescription signed by a certified practitioner. Please upload a clear photo or PDF document.
                </div>
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

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label">Upload Prescription File (PDF, JPG, PNG)</label>
                    <input type="file" name="prescription" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Order Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="99" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Preference</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="COD">Cash on Delivery (COD)</option>
                        <option value="Online">Online Payment</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 15px;">
                    Upload Document & Place Order 📄
                </button>

            </form>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse Pharmacy Network &bull; Certified Verification
    </footer>

</div>

</body>
</html>