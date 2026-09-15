<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$message = "";

// ADD INSURANCE
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $provider = $_POST['provider'];
    $policy = $_POST['policy'];
    $amount = $_POST['amount'];
    $valid = $_POST['valid'];

    $doc_path = "";

    // FILE UPLOAD
    if (!empty($_FILES['document']['name'])) {

        $file_name = time() . "_" . $_FILES['document']['name'];
        $tmp = $_FILES['document']['tmp_name'];

        $doc_path = "uploads/" . $file_name;

        move_uploaded_file($tmp, $doc_path);
    }

    $stmt = $conn->prepare("
        INSERT INTO insurance 
        (user_id, provider, policy_number, coverage_amount, valid_till, document)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ississ", $user_id, $provider, $policy, $amount, $valid, $doc_path);
    $stmt->execute();

    $message = "✅ Insurance Added Successfully";
}

// FETCH
$result = $conn->query("SELECT * FROM insurance WHERE user_id=$user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Insurance</title>

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
    min-height:100vh;
    padding:30px;
}

/* TOP BAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.back-btn{
    padding:8px 15px;
    background:#00c6ff;
    border-radius:20px;
    text-decoration:none;
    color:white;
}

.title{
    font-size:24px;
}

/* FORM BOX */
.box{
    background:rgba(255,255,255,0.1);
    padding:20px;
    border-radius:15px;
    width:350px;
    margin:auto;
}

input{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:none;
    border-radius:5px;
}

button{
    width:100%;
    padding:10px;
    background:#1161ee;
    border:none;
    color:white;
    border-radius:20px;
    cursor:pointer;
}

/* TABLE */
table{
    width:100%;
    margin-top:30px;
    border-collapse:collapse;
}

th, td{
    padding:10px;
    text-align:center;
}

th{ background:#1161ee; }
td{ background:rgba(255,255,255,0.05); }

.alert{
    text-align:center;
    margin:10px;
    color:#00ffcc;
}
</style>
</head>

<body>

<div class="overlay">

<!-- TOP BAR -->
<div class="topbar">
    <a href="dashboard.php" class="back-btn">⬅ Back</a>
    <div class="title">💳 Insurance</div>
</div>

<?php if($message): ?>
<div class="alert"><?php echo $message; ?></div>
<?php endif; ?>

<!-- FORM -->
<div class="box">

<form method="POST" enctype="multipart/form-data">

<input type="text" name="provider" placeholder="Provider" required>
<input type="text" name="policy" placeholder="Policy Number" required>
<input type="number" name="amount" placeholder="Coverage Amount" required>
<input type="date" name="valid" required>

<input type="file" name="document">

<button type="submit">Add Insurance</button>

</form>

</div>

<!-- TABLE -->
<table>

<tr>
<th>Provider</th>
<th>Policy</th>
<th>Coverage</th>
<th>Valid</th>
<th>Status</th>
<th>Doc</th>
<th>Claim</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {

while($row = $result->fetch_assoc()) {

    $today = date("Y-m-d");
    $status = ($row['valid_till'] >= $today) ? "🟢 Active" : "🔴 Expired";

    // Expiry warning
    $days_left = (strtotime($row['valid_till']) - time()) / (60*60*24);

    if ($days_left <= 7 && $days_left > 0) {
        $status .= " ⚠️ Expiring Soon";
    }

    // Claim estimation
    $claim = "₹" . ($row['coverage_amount'] * 0.8);

    $doc = $row['document'] 
        ? "<a href='{$row['document']}' target='_blank'>View</a>" 
        : "N/A";

    echo "<tr>
    <td>{$row['provider']}</td>
    <td>{$row['policy_number']}</td>
    <td>₹{$row['coverage_amount']}</td>
    <td>{$row['valid_till']}</td>
    <td>$status</td>
    <td>$doc</td>
    <td>$claim</td>
    </tr>";
}

} else {
echo "<tr><td colspan='7'>No data</td></tr>";
}
?>

</table>

</div>

</body>
</html>