<?php
session_start();

// ✅ OPTIONAL: handle logout redirect cleanly
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
}

// ✅ If already logged in → go dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediVerse - Smart Healthcare Platform</title>

<style>
body{
    margin:0;
    font-family:'Open Sans',sans-serif;
    background: url("hospital-bg.png") no-repeat center;
    background-size: cover;
    height:100vh;
    color:white;
}

/* DARK OVERLAY */
.overlay{
    background: rgba(0,0,0,0.7);
    height:100%;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
}

/* MAIN TITLE */
h1{
    font-size:48px;
    margin-bottom:10px;
}

/* GLOW */
.glow{
    font-size:22px;
    color:#00c6ff;
    animation: glow 2s infinite alternate;
}

@keyframes glow{
    from{ text-shadow:0 0 10px #00c6ff; }
    to{ text-shadow:0 0 25px #00c6ff; }
}

/* BUTTONS */
.buttons{
    margin-top:30px;
}

.btn{
    padding:12px 25px;
    margin:10px;
    border:none;
    border-radius:25px;
    font-size:16px;
    cursor:pointer;
    text-decoration:none;
    color:white;
    transition:0.3s;
}

.login-btn{ background:#1161ee; }
.register-btn{ background:#00c6ff; }

.btn:hover{
    transform: scale(1.1);
}

/* FEATURES */
.features{
    margin-top:50px;
    display:flex;
    gap:30px;
    flex-wrap:wrap;
    justify-content:center;
}

.feature-box{
    background: rgba(255,255,255,0.1);
    padding:20px;
    border-radius:15px;
    width:200px;
    backdrop-filter: blur(5px);
}

.feature-box:hover{
    transform:translateY(-5px);
}

/* FOOTER */
.footer{
    position:absolute;
    bottom:10px;
    text-align:center;
    font-size:14px;
    color:#ccc;
}
</style>
</head>

<body>

<div class="overlay">

    <h1>MediVerse</h1>
    <div class="glow">Smart Healthcare Platform</div>

    <div class="buttons">
        <!-- 🔴 FORCE CORRECT FLOW -->
        <a href="login.php" class="btn login-btn">Login</a>
        <a href="register.php" class="btn register-btn">Get Started</a>
    </div>

    <div class="features">
        <div class="feature-box">📅 Book Appointments</div>
        <div class="feature-box">🚑 Emergency Services</div>
        <div class="feature-box">📂 Health Records</div>
        <div class="feature-box">💊 Medicine Tracking</div>
    </div>

    <div class="footer">
        MediVerse © 2026 | Integrated Digital Healthcare Ecosystem
    </div>

</div>

</body>
</html>