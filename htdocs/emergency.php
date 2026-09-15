<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];

// Generate random details
$driver = ["Ramesh","Suresh","Mahesh","Arjun"];
$hospital = ["Apollo","KIMS","Care Hospital","Yashoda"];

$driver_name = $driver[array_rand($driver)];
$hospital_name = $hospital[array_rand($hospital)];
$ambulance_no = "AP09-" . rand(1000,9999);

// Save
$stmt = $conn->prepare("
INSERT INTO emergency_requests (user_id, driver_name, ambulance_no, hospital, status)
VALUES (?, ?, ?, ?, 'On the way')
");

$stmt->bind_param("isss", $user_id, $driver_name, $ambulance_no, $hospital_name);
$stmt->execute();

// Fetch family
$family = $conn->query("SELECT * FROM family_members WHERE user_id=$user_id");
?>

<!DOCTYPE html>
<html>
<head>
<title>Emergency</title>

<style>
body{
    margin:0;
    font-family:'Open Sans';
    background:black;
    color:white;
    text-align:center;
}
.box{
    margin-top:100px;
}
.track{
    margin-top:20px;
    padding:10px;
    background:green;
    width:300px;
    margin:auto;
    border-radius:10px;
}
</style>
</head>

<body>

<div class="box">

<h1>🚑 Ambulance Booked</h1>

<p><b>Driver:</b> <?php echo $driver_name; ?></p>
<p><b>Ambulance No:</b> <?php echo $ambulance_no; ?></p>
<p><b>Hospital:</b> <?php echo $hospital_name; ?></p>

<div class="track">
📍 Live Tracking: Ambulance is 5 mins away...
</div>

<h3>📲 Notified Family Members</h3>

<?php
if ($family->num_rows > 0) {
while($f = $family->fetch_assoc()) {
echo "<p>{$f['name']} ({$f['relation']}) - {$f['phone']}</p>";
}
} else {
echo "<p>No family contacts found</p>";
}
?>

<br><br>
<a href="dashboard.php" style="color:#00c6ff;">⬅ Back</a>

</div>

</body>
</html>