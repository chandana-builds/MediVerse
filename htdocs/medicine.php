<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

// Fetch medicines
$result = $conn->query("SELECT * FROM medicines");
?>

<!DOCTYPE html>
<html>
<head>
<title>Medicines - MediVerse</title>

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
    min-height:100vh;
    padding:30px;
}

h1{
    text-align:center;
}

/* BACK */
.back{
    display:inline-block;
    margin-bottom:20px;
    color:#00c6ff;
    text-decoration:none;
}

/* GRID */
.medicine-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
    gap:20px;
}

/* CARD */
.card{
    background:rgba(255,255,255,0.1);
    padding:20px;
    border-radius:15px;
    text-align:center;
    backdrop-filter:blur(6px);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

/* TEXT */
.price{
    color:#00ffcc;
    font-size:18px;
}

/* BUTTON */
.btn{
    padding:10px 20px;
    border:none;
    border-radius:20px;
    cursor:pointer;
    margin-top:10px;
    font-weight:bold;
}

.buy{
    background:#28a745;
    color:white;
}

.prescription{
    background:#ffc107;
    color:black;
}
</style>
</head>

<body>

<div class="overlay">

<a href="dashboard.php" class="back">⬅ Back to Dashboard</a>

<h1>💊 Available Medicines</h1>

<div class="medicine-grid">

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        echo "<div class='card'>
                <h3>{$row['name']}</h3>
                <p class='price'>₹{$row['price']}</p>
                <p>📍 {$row['store_location']}</p>";

        // 🔴 Prescription Required
        if ($row['requires_prescription']) {

            echo "<form action='upload_prescription.php' method='GET'>
                    <input type='hidden' name='medicine_id' value='{$row['id']}'>
                    <button type='submit' class='btn prescription'>Upload Prescription</button>
                  </form>";

        } 
        // 🟢 Direct Buy
        else {

            echo "<form action='checkout.php' method='GET'>
                    <input type='hidden' name='medicine_id' value='{$row['id']}'>
                    <button type='submit' class='btn buy'>Buy Now</button>
                  </form>";

        }

        echo "</div>";
    }
} else {
    echo "<p>No medicines found</p>";
}
?>

</div>

</div>

</body>
</html>