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

// ADD INSURANCE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $provider = trim($_POST['provider'] ?? '');
    $policy = trim($_POST['policy'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $valid = trim($_POST['valid'] ?? '');
    $doc_path = "";

    if (empty($provider) || empty($policy) || $amount <= 0 || empty($valid)) {
        $error = "Please fill in all required policy details.";
    } else {
        if (!empty($_FILES['document']['name']) && $_FILES['document']['error'] === 0) {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            $file_name = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['document']['name']));
            $doc_path = "uploads/" . $file_name;
            move_uploaded_file($_FILES['document']['tmp_name'], $doc_path);
        }

        $stmt = $conn->prepare("
            INSERT INTO insurance 
            (user_id, provider, policy_number, coverage_amount, valid_till, document)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issdss", $user_id, $provider, $policy, $amount, $valid, $doc_path);
        
        if ($stmt->execute()) {
            $message = "Insurance policy recorded successfully!";
        } else {
            $error = "Database Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// FETCH
$result = $conn->query("SELECT * FROM insurance WHERE user_id=$user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Insurance Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ins-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 25px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .ins-layout {
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
                <span class="brand-badge">INSURANCE</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <main class="container">

        <div style="margin-bottom: 25px;">
            <h2>💳 Health Insurance Portfolio</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Track coverage limits, expiration timelines, claim estimates, and policy documents.</p>
        </div>

        <div class="ins-layout">

            <!-- FORM PANEL -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">+ Add Insurance Policy</h3>

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
                        <label class="form-label">Provider Name</label>
                        <input type="text" name="provider" class="form-control" placeholder="e.g. Star Health, HDFC ERGO" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Policy Number</label>
                        <input type="text" name="policy" class="form-control" placeholder="e.g. POL-8899231" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total Coverage Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" placeholder="e.g. 500000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Valid Until</label>
                        <input type="date" name="valid" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Policy Document (Optional)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                        Save Insurance Policy 💳
                    </button>
                </form>
            </div>

            <!-- POLICIES LIST -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">📋 Active Policies</h3>

                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Provider / Policy</th>
                                <th>Coverage</th>
                                <th>Valid Till</th>
                                <th>Status</th>
                                <th>Claim Est. (80%)</th>
                                <th>Doc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <?php
                                        $today = date("Y-m-d");
                                        $isActive = ($row['valid_till'] >= $today);
                                        $days_left = (strtotime($row['valid_till']) - time()) / (60 * 60 * 24);
                                        $claim = "₹" . number_format($row['coverage_amount'] * 0.8, 2);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['provider']); ?></strong><br>
                                            <span style="font-size: 12px; color: var(--text-dim);"><?php echo htmlspecialchars($row['policy_number']); ?></span>
                                        </td>
                                        <td style="color: #34d399; font-weight: 700;">₹<?php echo number_format($row['coverage_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['valid_till']); ?></td>
                                        <td>
                                            <?php if ($isActive && $days_left <= 7): ?>
                                                <span class="badge badge-warning">⚠️ Expiring Soon</span>
                                            <?php elseif ($isActive): ?>
                                                <span class="badge badge-success">🟢 Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">🔴 Expired</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo $claim; ?></strong></td>
                                        <td>
                                            <?php if (!empty($row['document'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['document']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 11px;">View</a>
                                            <?php else: ?>
                                                <span style="color: var(--text-dim); font-size: 12px;">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-dim);">No insurance policies on record.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; Insurance & Claims Tracker
    </footer>

</div>

</body>
</html>