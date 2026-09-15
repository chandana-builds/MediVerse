<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $medicine = trim($_POST['medicine_name']);
    $time = $_POST['time_slot'];
    $notes = trim($_POST['notes']);

    if (!empty($medicine) && !empty($time)) {

        $stmt = $conn->prepare("
            INSERT INTO medicine_reminders (user_id, medicine_name, time_slot, notes)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param("isss", $user_id, $medicine, $time, $notes);
        $stmt->execute();

        $message = "✅ Reminder Added Successfully!";
    } else {
        $message = "⚠️ Please fill required fields";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Reminder</title>

<style>
body{
    margin:0;
    font-family:'Open Sans';
    background:url("hospital-bg.png") no-repeat center;
    background-size:cover;
    color:white;
}

.overlay{
    background:rgba(0,0,0,0.8);
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
    padding-top:30px;
}

/* BACK BUTTON */
.back-btn{
    align-self:flex-start;
    margin-left:30px;
    padding:8px 18px;
    background:#ff9800;
    border-radius:20px;
    text-decoration:none;
    color:white;
}

/* FORM BOX */
.box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    width:400px;
    margin-top:20px;
}

input, textarea{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:none;
    border-radius:10px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:20px;
    background:#00c6ff;
    color:white;
    cursor:pointer;
}

button:hover{
    background:#009ed1;
}

.alert{
    text-align:center;
    margin-top:10px;
    color:#00ffcc;
}
</style>
</head>

<body>

<div class="overlay">

<!-- 🔙 BACK -->
<a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<div class="box">
<h2>⏰ Add Medicine Reminder</h2>

<?php if($message): ?>
<div class="alert"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST">

<input type="text" name="medicine_name" placeholder="Medicine Name" required>

<input type="time" name="time_slot" required>

<textarea name="notes" placeholder="Notes (e.g., after food)"></textarea>

<button type="submit">Add Reminder</button>

</form>

</div>

</div>

</body>
</html>