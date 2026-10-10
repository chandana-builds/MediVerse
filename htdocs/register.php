<?php
session_start();
include_once __DIR__ . "/db.php";

$error = "";
$success = "";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check for existing username or email
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username or Email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed);

            if ($stmt->execute()) {
                $success = "Account created successfully! Redirecting to sign in...";
                echo "<script>setTimeout(() => { window.location='login.php'; }, 1500);</script>";
            } else {
                $error = "Failed to register account. Please try again.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Patient Registration</title>
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
            max-width: 480px;
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
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            padding: 12px;
            border-radius: var(--radius-md);
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
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
            <a href="login.php" class="btn btn-secondary btn-sm">Sign In</a>
        </nav>
    </header>

    <!-- AUTH FORM -->
    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-tabs">
                <a href="login.php" class="auth-tab">Sign In</a>
                <a href="register.php" class="auth-tab active">Sign Up</a>
            </div>

            <h2 style="font-size: 26px; margin-bottom: 8px;">Create Your Account</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 26px;">Join the MediVerse healthcare network for instant care.</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Choose a unique username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="yourname@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a strong password (min 6 chars)" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password to confirm" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                    Complete Registration 🚀
                </button>
            </form>

            <div style="margin-top: 24px; font-size: 13px; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary-light); font-weight: 600; text-decoration: none;">Sign in here</a>
            </div>

        </div>
    </div>

    <!-- FOOTER -->
    <footer class="med-footer">
        MediVerse &bull; Secure Healthcare Management
    </footer>

</div>

</body>
</html>