<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$message = "";

// ADD FAMILY MEMBER
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $relation = $_POST['relation'];
    $phone = $_POST['phone'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("
        INSERT INTO family_members (user_id, name, relation, phone, availability)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("issss", $user_id, $name, $relation, $phone, $availability);
    $stmt->execute();

    $message = "✅ Family member added!";
}

// FETCH
$result = $conn->query("SELECT * FROM family_members WHERE user_id=$user_id");
?>

<!DOCTYPE html>
<html>
<head>
<title>Family Details</title>

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
}
button{
    width:100%;
    padding:10px;
    background:#1161ee;
    border:none;
    color:white;
}
table{
    width:100%;
    margin-top:30px;
}
th,td{
    padding:10px;
    text-align:center;
}
th{ background:#1161ee; }
td{ background:rgba(255,255,255,0.05); }

.back{
    margin-top:20px;
    text-align:center;
}
.back a{
    color:#00c6ff;
    text-decoration:none;
}
</style>
</head>

<body>
<div class="overlay">

<h2>👨‍👩‍👧 Family Contacts</h2>

<?php if($message) echo "<p>$message</p>"; ?>

<div class="box">
<form method="POST">
<input type="text" name="name" placeholder="Name" required>
<input type="text" name="relation" placeholder="Relation" required>
<input type="text" name="phone" placeholder="Phone" required>
<input type="text" name="availability" placeholder="Availability (location/time)">
<button>Add</button>
</form>
</div>

<table>
<tr>
<th>Name</th>
<th>Relation</th>
<th>Phone</th>
<th>Availability</th>
</tr>

<?php
if ($result->num_rows > 0) {
while($row = $result->fetch_assoc()) {
echo "<tr>
<td>{$row['name']}</td>
<td>{$row['relation']}</td>
<td>{$row['phone']}</td>
<td>{$row['availability']}</td>
</tr>";
}
} else {
echo "<tr><td colspan='4'>No family added</td></tr>";
}
?>
</table>

<div class="back">
<a href="dashboard.php">⬅ Back to Dashboard</a>
</div>

</div>
</body>
</html>