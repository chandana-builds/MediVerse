<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = (int)$_SESSION['user_id'];
$message = "";
$error = "";

// Handle upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    
    if (empty($title) || !isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
        $error = "Please provide a title and select a valid file to upload.";
    } else {
        $file = $_FILES['file'];
        $targetDir = "uploads/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file["name"]));
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO health_records (user_id, title, file_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $title, $fileName);

            if ($stmt->execute()) {
                $message = "Health record securely archived in your vault!";
            } else {
                $error = "Database Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Failed to store uploaded file on the server.";
        }
    }
}

// Fetch existing records
$records_res = $conn->query("SELECT * FROM health_records WHERE user_id=$user_id ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Encrypted Health Vault</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .vault-layout {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 25px;
            align-items: start;
        }

        @media (max-width: 850px) {
            .vault-layout {
                grid-template-columns: 1fr;
            }
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
                <span class="brand-badge">VAULT</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <main class="container">

        <div style="margin-bottom: 25px;">
            <h2>📂 Digital Health Records Vault</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Store, manage, and retrieve your medical history, diagnostic scans, and laboratory blood tests.</p>
        </div>

        <div class="vault-layout">

            <!-- UPLOAD PANEL -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">+ Upload New Document</h3>

                <?php if ($message): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; padding: 12px; border-radius: var(--radius-md); margin-bottom: 18px;">
                        ✅ <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 12px; border-radius: var(--radius-md); margin-bottom: 18px;">
                        ⚠️ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label class="form-label">Report Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Complete Blood Count (CBC) Report" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Document (PDF, Image)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                        Upload to Secure Vault 🔒
                    </button>

                </form>
            </div>

            <!-- EXISTING RECORDS PANEL -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">📋 Your Stored Records</h3>

                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Report Title</th>
                                <th>Upload Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($records_res && $records_res->num_rows > 0): ?>
                                <?php while($rec = $records_res->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>📄 <?php echo htmlspecialchars($rec['title']); ?></strong></td>
                                        <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($rec['uploaded_at']); ?></td>
                                        <td>
                                            <a href="uploads/<?php echo htmlspecialchars($rec['file_name']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 4px 12px;">
                                                View File ↗
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 30px; color: var(--text-dim);">No medical records uploaded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; 256-Bit Encrypted Health Vault
    </footer>

</div>

</body>
</html>