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

// Handle form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicine = trim($_POST['medicine_name'] ?? '');
    $time = trim($_POST['time_slot'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!empty($medicine) && !empty($time)) {
        $stmt = $conn->prepare("
            INSERT INTO medicine_reminders (user_id, medicine_name, time_slot, notes)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $medicine, $time, $notes);
        if ($stmt->execute()) {
            $message = "Medication reminder scheduled successfully!";
        } else {
            $error = "Error adding reminder: " . $stmt->error;
        }
    } else {
        $error = "Please provide medicine name and time slot.";
    }
}

// Fetch user reminders
$rem_res = $conn->query("SELECT * FROM medicine_reminders WHERE user_id=$user_id ORDER BY time_slot ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Medicine Reminders</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reminders-layout {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 25px;
            align-items: start;
        }

        @media (max-width: 850px) {
            .reminders-layout {
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
                <span class="brand-badge">REMINDERS</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <main class="container">

        <div style="margin-bottom: 25px;">
            <h2>⏰ Medication Schedule & Reminders</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Set daily prescription intake alerts to maintain consistent dosage.</p>
        </div>

        <div class="reminders-layout">

            <!-- FORM PANEL -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">+ Add New Reminder</h3>

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

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Medicine Name</label>
                        <input type="text" name="medicine_name" class="form-control" placeholder="e.g. Paracetamol 650mg" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Intake Time</label>
                        <input type="time" name="time_slot" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions / Dosage Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="e.g. Take 1 tablet after breakfast with water"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                        Save Reminder ⏰
                    </button>
                </form>
            </div>

            <!-- EXISTING REMINDERS LIST -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">📋 Your Scheduled Meds</h3>

                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Time</th>
                                <th>Clinical Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rem_res && $rem_res->num_rows > 0): ?>
                                <?php while($r = $rem_res->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>💊 <?php echo htmlspecialchars($r['medicine_name']); ?></strong></td>
                                        <td><span class="badge badge-warning">⏰ <?php echo htmlspecialchars($r['time_slot']); ?></span></td>
                                        <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($r['notes'] ?: 'None'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 30px; color: var(--text-dim);">No active reminders recorded.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; Digital Prescription Reminders
    </footer>

</div>

</body>
</html>