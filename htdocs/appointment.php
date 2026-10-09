<?php
session_start();

include_once __DIR__ . "/db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $hospital = $_POST['hospital'];
    $department = $_POST['department'];
    $doctor = $_POST['doctor'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Basic validation
    if (empty($time)) {
        echo "<script>alert('Please select a time slot');</script>";
    } else {

        $stmt = $conn->prepare("INSERT INTO appointments 
        (user_id, hospital, department, doctor_name, appointment_date, appointment_time)
        VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("isssss", $user_id, $hospital, $department, $doctor, $date, $time);

        if ($stmt->execute()) {
            echo "<script>alert('Appointment Booked Successfully!'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Error booking appointment');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment</title>

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
    display:flex;
    justify-content:center;
    align-items:center;
}

.form-box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    width:400px;
    backdrop-filter: blur(6px);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

.group{
    margin-bottom:15px;
}

label{
    font-size:14px;
}

select, input{
    width:100%;
    padding:10px;
    margin-top:5px;
    border:none;
    border-radius:10px;
}

.slots{
    display:grid;
    grid-template-columns: repeat(2,1fr);
    gap:10px;
    margin-top:10px;
}

.slot{
    padding:10px;
    background:#1161ee;
    border-radius:10px;
    text-align:center;
    cursor:pointer;
}

.slot:hover{
    background:#0d4fc0;
}

button{
    width:100%;
    padding:12px;
    margin-top:20px;
    background:#00c6ff;
    border:none;
    border-radius:25px;
    color:white;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#009acb;
}

.back{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#0dcaf0;
    text-decoration:none;
}
</style>

<script>
// Dynamic doctors based on department
function updateDoctors(){
    let dept = document.getElementById("department").value;
    let doctor = document.getElementById("doctor");

    let options = "";

    if(dept === "General Medicine"){
        options = "<option>Dr. Reddy</option><option>Dr. Kumar</option>";
    }
    else if(dept === "Cardiology"){
        options = "<option>Dr. Sharma</option>";
    }
    else if(dept === "Neurology"){
        options = "<option>Dr. Mehta</option>";
    }

    doctor.innerHTML = options;
}

// Slot selection
function selectSlot(event, time){
    document.getElementById("selected_time").value = time;

    let slots = document.querySelectorAll(".slot");
    slots.forEach(s => s.style.background = "#1161ee");

    if (event && event.currentTarget) {
        event.currentTarget.style.background = "green";
    }
}
</script>

</head>

<body>

<div class="overlay">
<div class="form-box">

<h2>📅 Book Appointment</h2>

<form method="POST">

    <!-- Hospital -->
    <div class="group">
        <label>Select Hospital</label>
        <select name="hospital" required>
            <option value="">-- Choose Hospital --</option>
            <option>City Care Hospital</option>
            <option>MediLife Clinic</option>
            <option>Sunrise Hospital</option>
        </select>
    </div>

    <!-- Department -->
    <div class="group">
        <label>Department</label>
        <select name="department" id="department" onchange="updateDoctors()" required>
            <option value="">Select Department</option>
            <option>General Medicine</option>
            <option>Cardiology</option>
            <option>Neurology</option>
        </select>
    </div>

    <!-- Doctor -->
    <div class="group">
        <label>Doctor</label>
        <select name="doctor" id="doctor" required>
            <option>Select Department First</option>
        </select>
    </div>

    <!-- Date -->
    <div class="group">
        <label>Preferred Date</label>
        <input type="date" name="date" required>
    </div>

    <!-- Slots -->
    <div class="group">
        <label>Available Slots</label>

        <div class="slots">
            <div class="slot" onclick="selectSlot(event, '09:00 AM')">09:00 AM</div>
            <div class="slot" onclick="selectSlot(event, '10:30 AM')">10:30 AM</div>
            <div class="slot" onclick="selectSlot(event, '02:00 PM')">02:00 PM</div>
            <div class="slot" onclick="selectSlot(event, '04:30 PM')">04:30 PM</div>
            <div class="slot" onclick="selectSlot(event, '06:00 PM')">06:00 PM</div>
        </div>

        <input type="hidden" name="time" id="selected_time" required>
    </div>

    <button type="submit">Confirm Booking</button>

</form>

<a href="dashboard.php" class="back">← Back to Dashboard</a>

</div>
</div>

</body>
</html>