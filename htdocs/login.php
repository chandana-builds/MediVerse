<?php
ob_start(); // 🔴 Prevent header issues
session_start();
include "db.php";

// ✅ If already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data (username or email/gmail)
    $login_input = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        echo "<script>alert('All fields are required');</script>";
    } else {

        // Prepare secure query supporting Username or Email (Gmail)
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $login_input, $login_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $row = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $row['password'])) {

                // Create session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];

                // ✅ Redirect to dashboard
                header("Location: dashboard.php");
                exit();

            } else {
                echo "<script>alert('❌ Incorrect password');</script>";
            }

        } else {
            echo "<script>alert('❌ User not found with that Username or Email');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediVerse Login</title>

<style>
body{
    margin:0;
    font-family:'Open Sans',sans-serif;
    background: url("hospital-bg.png") no-repeat center;
    background-size: cover;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-wrap{
    width:100%;
    max-width:525px;
    min-height:650px;
    background: url("doctor.png") no-repeat center;
    background-size: cover;
    box-shadow: inset 0 0 200px rgba(0,0,0,0.3);
    border-radius:20px;
    overflow:hidden;
    display:flex;
}

.login-php{
    width:100%;
    padding:60px 50px;
    background:rgba(0,0,0,0.6);
    color:#fff;
}

.tabs{
    display:flex;
    gap:20px;
    margin-bottom:40px;
}

.tab{
    color:#aaa;
    font-size:20px;
    border-bottom:2px solid transparent;
    padding-bottom:5px;
    text-decoration:none;
}

.tab.active{
    color:#fff;
    border-color:#1161ee;
}

.group{
    margin-bottom:20px;
}

.label{
    color:#fff;
    font-size:13px;
    text-transform:uppercase;
}

.input{
    width:100%;
    padding:12px;
    border-radius:25px;
    border:none;
    background:rgba(255,255,255,0.1);
    color:white;
    margin-top:5px;
}

.button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:25px;
    background:#1161ee;
    color:white;
    font-size:16px;
    cursor:pointer;
}

.button:hover{
    background:#0d4fc0;
}

.footer{
    text-align:center;
    margin-top:20px;
}

.footer a{
    color:#0dcaf0;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="login-wrap">
<div class="login-php">

<div class="tabs">
    <a href="login.php" class="tab active">Sign In</a>
    <a href="register.php" class="tab">Sign Up</a>
</div>

<form method="POST">

    <div class="group">
        <label class="label">Username or Email (Gmail)</label>
        <input type="text" name="username" class="input" placeholder="Enter username or email" required>
    </div>

    <div class="group">
        <label class="label">Password</label>
        <input type="password" name="password" class="input" required>
    </div>

    <div class="group">
        <button type="submit" class="button">Sign In</button>
    </div>

</form>

<div class="footer">
    Don't have an account? <a href="register.php">Sign Up</a>
</div>

</div>
</div>

</body>
</html>