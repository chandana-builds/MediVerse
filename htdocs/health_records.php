<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

// Handle upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];

    $file = $_FILES['file'];
    $fileName = time() . "_" . basename($file["name"]);
    $targetDir = "uploads/";
    $targetFile = $targetDir . $fileName;

    // Create folder if not exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir);
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {

        $stmt = $conn->prepare("INSERT INTO health_records (user_id, title, file_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $fileName);

        if ($stmt->execute()) {
            echo "<script>alert('File Uploaded Successfully');</script>";
        } else {
            echo "<script>alert('Database Error');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('File Upload Failed');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Health Records</title>

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

.box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    width:350px;
}

input, button{
    width:100%;
    padding:10px;
    margin-top:10px;
    border:none;
    border-radius:10px;
}

button{
    background:#1161ee;
    color:white;
}

.back{
    display:block;
    margin-top:15px;
    text-align:center;
    color:#0dcaf0;
}
</style>

</head>

<body>

<div class="overlay">
<div class="box">

<h2>📂 Upload Health Record</h2>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Report Title" required>

    <input type="file" name="file" required>

    <button type="submit">Upload</button>

</form>

<a href="dashboard.php" class="back">← Back</a>

</div>
</div>

</body>
</html>