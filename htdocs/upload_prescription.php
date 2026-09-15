<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

// ✅ Validate medicine_id
if (!isset($_GET['medicine_id']) || !is_numeric($_GET['medicine_id'])) {
    die("❌ Invalid Medicine ID");
}

$medicine_id = intval($_GET['medicine_id']);

// Fetch medicine safely
$stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
$stmt->bind_param("i", $medicine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("❌ Medicine Not Found");
}

$medicine = $result->fetch_assoc();

// ✅ Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $medicine_name = $medicine['name'];
    $store_name = $medicine['store_location'];
    $quantity = intval($_POST['quantity']);
    $payment_method = $_POST['payment_method'];

    // ✅ File Upload Validation
    if (!isset($_FILES['prescription']) || $_FILES['prescription']['error'] !== 0) {
        die("❌ File upload error");
    }

    $file = $_FILES['prescription'];

    // Allow only PDF/JPG/PNG
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed_types)) {
        die("❌ Only PDF, JPG, PNG allowed");
    }

    // Limit file size (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        die("❌ File too large (Max 2MB)");
    }

    // Create uploads folder if not exists
    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    // Unique file name
    $new_file_name = time() . "_" . basename($file['name']);
    $upload_path = "uploads/" . $new_file_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {

        // ✅ Insert using prepared statement
        $stmt = $conn->prepare("
            INSERT INTO medicine_orders 
            (user_id, medicine_name, quantity, store_name, payment_method, prescription_required, prescription_file)
            VALUES (?, ?, ?, ?, ?, 1, ?)
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
            echo "<script>alert('✅ Prescription Uploaded & Order Placed'); window.location='dashboard.php';</script>";
        } else {
            echo "DB Error: " . $stmt->error;
        }

    } else {
        echo "❌ File upload failed";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Prescription</title>

<style>
body{
    margin:0;
    font-family:'Open Sans';
    background:url("hospital-bg.png") no-repeat center;
    background-size:cover;
    color:white;
}

.overlay{
    background:rgba(0,0,0,0.85);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.form-box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    width:320px;
    text-align:center;
    backdrop-filter: blur(6px);
}

input, select{
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:10px;
    border:none;
}

button{
    width:100%;
    padding:12px;
    background:#28a745;
    border:none;
    border-radius:20px;
    color:white;
    font-size:15px;
    cursor:pointer;
}

button:hover{
    background:#1e7e34;
}
</style>
</head>

<body>

<div class="overlay">

<div class="form-box">

<h2>📄 Upload Prescription</h2>

<p><b><?php echo $medicine['name']; ?></b></p>
<p>📍 <?php echo $medicine['store_location']; ?></p>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="prescription" required>

<label>Quantity</label>
<input type="number" name="quantity" value="1" min="1" required>

<label>Payment</label>
<select name="payment_method">
<option value="COD">Cash on Delivery</option>
<option value="Online">Online Payment</option>
</select>

<button type="submit">Upload & Order</button>

</form>

</div>
</div>

</body>
</html>