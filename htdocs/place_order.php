<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $medicine_name = $_POST['medicine_name'];
    $quantity = $_POST['quantity'];
    $store_name = $_POST['store_name'];
    $payment_method = $_POST['payment_method'];

    // ✅ Prescription flag validation
    $prescription_required = isset($_POST['prescription_required']) ? intval($_POST['prescription_required']) : 0;

    // Insert into DB using prepared statement
    $stmt = $conn->prepare("
        INSERT INTO medicine_orders 
        (user_id, medicine_name, quantity, store_name, payment_method, prescription_required, prescription_file, order_status)
        VALUES (?, ?, ?, ?, ?, ?, NULL, 'Pending')
    ");

    $stmt->bind_param("isissi", $user_id, $medicine_name, $quantity, $store_name, $payment_method, $prescription_required);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Order Placed Successfully'); window.location='dashboard.php';</script>";
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>