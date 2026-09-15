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

    // ✅ IMPORTANT FIX (added)
    $prescription_required = $_POST['prescription_required'];

    // (Optional for future use)
    $prescription_file = NULL;

    // Insert into DB
    $sql = "INSERT INTO medicine_orders 
            (user_id, medicine_name, quantity, store_name, payment_method, prescription_required, prescription_file)
            VALUES 
            ('$user_id', '$medicine_name', '$quantity', '$store_name', '$payment_method', '$prescription_required', '$prescription_file')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('✅ Order Placed Successfully'); window.location='dashboard.php';</script>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}
?>