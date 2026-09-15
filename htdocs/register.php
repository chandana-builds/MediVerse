<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data safely
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        echo "<script>alert('All fields are required');</script>";
    }
    elseif ($password !== $confirm) {
        echo "<script>alert('Passwords do not match');</script>";
    }
    else {

        // Check if username or email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Username or Email already registered');</script>";
        } else {

            // Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration Successful');
                        window.location='login.php';
                      </script>";
            } else {
                echo "<script>alert('Something went wrong');</script>";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediVerse Register</title>

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
    background:  url("doctor.png") no-repeat center;
    background-size: cover;
    box-shadow: inset 0 0 200px rgba(0,0,0,0.3);
    border-radius:20px;
    overflow:hidden;
    display: flex;
    position:relative;
}

.login-php{
    width: 100%;
    flex-grow: 1;
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
    text-transform: uppercase;
    font-size:13px;
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
    <a href="login.php" class="tab">Sign In</a>
    <a href="register.php" class="tab active">Sign Up</a>
</div>

<form method="POST">
    <div class="group">
        <label class="label">Username</label>
        <input type="text" name="username" class="input" required>
    </div>

    <div class="group">
        <label class="label">Email (Gmail)</label>
        <input type="email" name="email" class="input" placeholder="e.g. yourname@gmail.com" required>
    </div>

    <div class="group">
        <label class="label">Password</label>
        <input type="password" name="password" class="input" required>
    </div>

    <div class="group">
        <label class="label">Confirm Password</label>
        <input type="password" name="confirm_password" class="input" required>
    </div>

    <div class="group">
        <button type="submit" class="button">Sign Up</button>
    </div>
</form>

<div class="footer">
    Already have an account? <a href="login.php">Sign In</a>
</div>

</div>
</div>

</body>
</html>