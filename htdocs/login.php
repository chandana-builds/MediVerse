<?php
ob_start();
session_start();
include_once __DIR__ . "/db.php";

$error = "";

// If already logged in -> go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        // Query supporting Username or Email
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $login_input, $login_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No user found with that username or email address.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Patient Sign In</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 140px);
            padding: 30px 20px;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: 40px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
            text-align: center;
            position: relative;
        }

        .auth-tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: var(--radius-full);
            padding: 4px;
            margin-bottom: 28px;
            border: 1px solid var(--border-subtle);
        }

        .auth-tab {
            flex: 1;
            padding: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: var(--radius-full);
            transition: var(--transition);
        }

        .auth-tab.active {
            background: #ffffff;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            font-weight: 700;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px;
            border-radius: var(--radius-md);
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-box {
            background: rgba(14, 165, 233, 0.08);
            border: 1px dashed rgba(14, 165, 233, 0.3);
            border-radius: var(--radius-md);
            padding: 12px;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 24px;
            text-align: left;
        }

        .demo-box strong {
            color: var(--primary-light);
        }
    </style>
</head>
<body>

<div class="page-backdrop">

    <!-- NAVBAR -->
    <header class="med-navbar">
        <a href="index.php" class="brand-wrapper">
            <div class="brand-logo-icon">🩺</div>
            <div class="brand-name">
                MediVerse
                <span class="brand-badge">PORTAL</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="index.php" class="nav-link">Home</a>
            <a href="register.php" class="btn btn-primary btn-sm">Create Account</a>
        </nav>
    </header>

    <!-- AUTH FORM -->
    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-tabs">
                <a href="login.php" class="auth-tab active">Sign In</a>
                <a href="register.php" class="auth-tab">Sign Up</a>
            </div>

            <h2 style="font-size: 26px; margin-bottom: 8px;">Welcome Back</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 26px;">Sign in to access your personal medical dashboard.</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username or email" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                    Sign In to Portal →
                </button>
            </form>

            <div style="margin-top: 24px; font-size: 13px; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="color: var(--primary-light); font-weight: 600; text-decoration: none;">Create one now</a>
            </div>

        </div>
    </div>

    <!-- FOOTER -->
    <footer class="med-footer">
        MediVerse &bull; Secure HIPAA-Compliant Healthcare Portal
    </footer>

</div>

</body>
</html>