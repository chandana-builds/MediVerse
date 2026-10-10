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

// ADD FAMILY MEMBER
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $relation = trim($_POST['relation'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $availability = trim($_POST['availability'] ?? '');

    if (empty($name) || empty($relation) || empty($phone)) {
        $error = "Please provide name, relation, and contact phone number.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO family_members (user_id, name, relation, phone, availability)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $user_id, $name, $relation, $phone, $availability);
        
        if ($stmt->execute()) {
            $message = "Family emergency contact added!";
        } else {
            $error = "Database Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// FETCH
$result = $conn->query("SELECT * FROM family_members WHERE user_id=$user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Family Emergency Circle</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .family-layout {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 25px;
            align-items: start;
        }

        @media (max-width: 850px) {
            .family-layout {
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
                <span class="brand-badge">FAMILY CIRCLE</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <main class="container">

        <div style="margin-bottom: 25px;">
            <h2>👨‍👩‍👧 Family Emergency Circle</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Contacts automatically notified during SOS ambulance requests and medical emergencies.</p>
        </div>

        <div class="family-layout">

            <!-- FORM PANEL -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">+ Add Family Contact</h3>

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
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Sharma" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="relation" class="form-control" placeholder="e.g. Father, Spouse, Sibling" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+91 9876543210" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Availability / Location</label>
                        <input type="text" name="availability" class="form-control" placeholder="e.g. 24/7 Home, Downtown">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                        Add to Emergency Circle 👨‍👩‍👧
                    </button>
                </form>
            </div>

            <!-- CONTACTS LIST -->
            <div class="glass-panel">
                <h3 style="font-size: 20px; margin-bottom: 18px;">📋 Emergency Contact Network</h3>

                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relation</th>
                                <th>Contact</th>
                                <th>Availability</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['relation']); ?></span></td>
                                        <td><a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" style="color: #38bdf8; text-decoration: none; font-weight: 600;">📞 <?php echo htmlspecialchars($row['phone']); ?></a></td>
                                        <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($row['availability'] ?: 'Standard'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-dim);">No family emergency contacts registered.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; Family Care & Emergency Notification Network
    </footer>

</div>

</body>
</html>