<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

// ✅ Validate medicine_id
if (!isset($_GET['medicine_id']) || empty($_GET['medicine_id'])) {
    die("❌ Invalid Medicine Access");
}

$medicine_id = intval($_GET['medicine_id']);

// ✅ Fetch medicine safely
$stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
$stmt->bind_param("i", $medicine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Medicine Not Found");
}

$medicine = $result->fetch_assoc();

// ✅ Handle Order
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $quantity = intval($_POST['quantity']);
    $payment_method = $_POST['payment_method'];

    if ($quantity <= 0) {
        die("❌ Invalid Quantity");
    }

    $medicine_name = $medicine['name'];
    $store_name = $medicine['store_location'];

    // ✅ Insert with status
    $stmt = $conn->prepare("
        INSERT INTO medicine_orders 
        (user_id, medicine_name, quantity, store_name, payment_method, order_status) 
        VALUES (?, ?, ?, ?, ?, 'Pending')
    ");

    $stmt->bind_param("isiss", $user_id, $medicine_name, $quantity, $store_name, $payment_method);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Order Placed Successfully'); window.location='dashboard.php';</script>";
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>

<style>
body{
    margin:0;
    font-family:'Open Sans',sans-serif;
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

/* BOX */
.box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    width:320px;
    text-align:center;
    backdrop-filter: blur(6px);
}

/* INPUT */
input, select{
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:10px;
    border:none;
}

/* BUTTON */
button{
    padding:12px;
    width:100%;
    background:#28a745;
    border:none;
    color:white;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    background:#218838;
}

/* BACK */
.back{
    display:block;
    margin-bottom:10px;
    color:#00c6ff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="overlay">

<div class="box">

<a href="medicine.php" class="back">⬅ Back</a>

<h2><?php echo htmlspecialchars($medicine['name']); ?></h2>
<p>📍 <?php echo htmlspecialchars($medicine['store_location']); ?></p>
<p>💰 ₹<?php echo htmlspecialchars($medicine['price']); ?></p>

<form method="POST">

<label>Quantity</label>
<input type="number" name="quantity" value="1" min="1" required>

<label>Payment Method</label>
<select name="payment_method">
    <option value="COD">Cash on Delivery</option>
    <option value="Online">Online Payment</option>
</select>

<button type="submit">Confirm Order</button>

</form>

</div>

</div>

</body>
</html>