<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Queries
$result = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date DESC");
$records = $conn->query("SELECT * FROM health_records WHERE user_id = $user_id ORDER BY uploaded_at DESC");
$reminders = $conn->query("SELECT * FROM medicine_reminders WHERE user_id = $user_id ORDER BY time_slot ASC");
$orders = $conn->query("SELECT * FROM medicine_orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>MediVerse Dashboard</title>

<style>
body{
    margin:0;
    font-family:'Open Sans',sans-serif;
    background:url("hospital-bg.png") no-repeat center;
    background-size:cover;
    color:white;
}

.overlay{
    background:rgba(0,0,0,0.75);
    min-height:100vh;
    padding:20px;
}

/* TOPBAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logout{
    padding:8px 15px;
    background:red;
    border-radius:20px;
    text-decoration:none;
    color:white;
}

/* WELCOME */
.welcome{
    margin:20px 0;
    font-size:22px;
}

/* GRID */
.dashboard-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap:20px;
}

.card{
    background:rgba(255,255,255,0.1);
    padding:25px;
    border-radius:15px;
    text-align:center;
    backdrop-filter: blur(6px);
    transition:0.3s;
    text-decoration:none;
    color:white;
}

.card:hover{
    transform:scale(1.05);
    background:rgba(255,255,255,0.2);
}

/* EMERGENCY */
.emergency{
    margin:30px 0;
    text-align:center;
}

.emergency button{
    background:red;
    padding:18px 40px;
    border:none;
    border-radius:50px;
    font-size:20px;
    color:white;
    cursor:pointer;
}

/* DATA GRID */
.data-grid{
    display:grid;
    grid-template-columns: repeat(2,1fr);
    gap:20px;
}

/* BOX */
.box{
    background:rgba(0,0,0,0.7);
    padding:20px;
    border-radius:15px;
    overflow-x:auto;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#1161ee;
    padding:10px;
}

td{
    padding:10px;
    text-align:center;
    background:rgba(255,255,255,0.05);
}

tr:hover td{
    background:rgba(255,255,255,0.15);
}

/* RESPONSIVE */
@media(max-width:768px){
    .dashboard-grid, .data-grid{
        grid-template-columns:1fr;
    }
}
</style>

<script>
// 🚑 Emergency Flow
function triggerEmergency() {
    let confirmAction = confirm("🚑 Do you want to request an ambulance?");
    
    if (confirmAction) {
        alert("🚑 Ambulance Booked Successfully!");
        window.location.href = "emergency.php"; // next page
    }
}
</script>

</head>

<body>

<div class="overlay">

<!-- TOP -->
<div class="topbar">
    <h2>MediVerse Dashboard</h2>
    <a href="logout.php" class="logout">Logout</a>
</div>

<!-- WELCOME -->
<div class="welcome">
    Welcome, <?php echo $username; ?> 👋
</div>

<!-- FEATURES -->
<div class="dashboard-grid">

<a href="appointment.php" class="card">📅 <h3>Appointments</h3></a>
<a href="health_records.php" class="card">📂 <h3>Health Records</h3></a>
<a href="medicine.php" class="card">💊 <h3>Buy Medicines</h3></a>
<a href="reminder.php" class="card">⏰ <h3>Reminders</h3></a>
<a href="symptom.php" class="card">🧠 <h3>Symptom Checker</h3></a>
<a href="insurance.php" class="card">💳 <h3>Insurance</h3></a>

<!-- ✅ FEATURES -->
<a href="family.php" class="card">👨‍👩‍👧 <h3>Family Members</h3></a>
<a href="database.php" class="card" style="border: 1px solid #00c6ff;">🗄️ <h3>Database Viewer</h3></a>

</div>

<!-- EMERGENCY -->
<div class="emergency">
<button onclick="triggerEmergency()">
🚑 EMERGENCY
</button>
</div>

<!-- DATA -->
<div class="data-grid">

<!-- APPOINTMENTS -->
<div class="box">
<h2>📋 Appointments</h2>
<table>
<tr><th>Hospital</th><th>Dept</th><th>Doctor</th><th>Date</th><th>Time</th></tr>

<?php
if ($result && $result->num_rows > 0) {
while($row = $result->fetch_assoc()) {
echo "<tr>
<td>{$row['hospital']}</td>
<td>{$row['department']}</td>
<td>{$row['doctor_name']}</td>
<td>{$row['appointment_date']}</td>
<td>{$row['appointment_time']}</td>
</tr>";
}
} else {
echo "<tr><td colspan='5'>No data</td></tr>";
}
?>
</table>
</div>

<!-- RECORDS -->
<div class="box">
<h2>📂 Records</h2>
<table>
<tr><th>Title</th><th>File</th><th>Date</th></tr>

<?php
if ($records && $records->num_rows > 0) {
while($row = $records->fetch_assoc()) {
echo "<tr>
<td>{$row['title']}</td>
<td><a href='uploads/{$row['file_name']}' target='_blank'>View</a></td>
<td>{$row['uploaded_at']}</td>
</tr>";
}
} else {
echo "<tr><td colspan='3'>No records</td></tr>";
}
?>
</table>
</div>

<!-- REMINDERS -->
<div class="box">
<h2>⏰ Reminders</h2>
<table>
<tr><th>Medicine</th><th>Time</th><th>Notes</th></tr>

<?php
if ($reminders && $reminders->num_rows > 0) {
while($row = $reminders->fetch_assoc()) {
echo "<tr>
<td>{$row['medicine_name']}</td>
<td>{$row['time_slot']}</td>
<td>{$row['notes']}</td>
</tr>";
}
} else {
echo "<tr><td colspan='3'>No reminders</td></tr>";
}
?>
</table>
</div>

<!-- ORDERS -->
<div class="box">
<h2>💊 Orders</h2>
<table>
<tr><th>Medicine</th><th>Qty</th><th>Store</th><th>Prescription</th><th>Payment</th></tr>

<?php
if ($orders && $orders->num_rows > 0) {
while($row = $orders->fetch_assoc()) {

$prescription = ($row['prescription_required'] && !empty($row['prescription_file']))
? "<a href='{$row['prescription_file']}' target='_blank'>View</a>"
: "Not Required";

echo "<tr>
<td>{$row['medicine_name']}</td>
<td>{$row['quantity']}</td>
<td>{$row['store_name']}</td>
<td>$prescription</td>
<td>{$row['payment_method']}</td>
</tr>";
}
} else {
echo "<tr><td colspan='5'>No orders</td></tr>";
}
?>
</table>
</div>

</div>

</div>

</body>
</html>