<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = (int)$_SESSION['user_id'];

// Generate emergency dispatch details
$drivers = ["Officer Ramesh V.", "Officer Suresh K.", "Officer Arjun M.", "Officer Priya D."];
$hospitals = ["Apollo Emergency Trauma Care", "City Care Multi-Speciality Hospital", "KIMS Critical Care Unit", "Sunrise Super-Speciality Hospital"];

$driver_name = $drivers[array_rand($drivers)];
$hospital_name = $hospitals[array_rand($hospitals)];
$ambulance_no = "MED-SOS-" . rand(100, 999);

// Record the emergency request
$stmt = $conn->prepare("
    INSERT INTO emergency_requests (user_id, driver_name, ambulance_no, hospital, status)
    VALUES (?, ?, ?, ?, 'Dispatched & En Route')
");
$stmt->bind_param("isss", $user_id, $driver_name, $ambulance_no, $hospital_name);
$stmt->execute();
$stmt->close();

// Fetch family contacts
$family = $conn->query("SELECT * FROM family_members WHERE user_id=$user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Emergency Ambulance Dispatched</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sos-wrapper {
            max-width: 650px;
            margin: 30px auto;
            text-align: center;
        }

        .sos-header-card {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(185, 28, 28, 0.3));
            border: 2px solid rgba(239, 68, 68, 0.5);
            border-radius: var(--radius-xl);
            padding: 35px 25px;
            margin-bottom: 25px;
            box-shadow: 0 0 40px rgba(239, 68, 68, 0.3);
            animation: pulse-sos 2.5s infinite;
        }

        .eta-tracker {
            background: rgba(16, 185, 129, 0.18);
            border: 1px solid rgba(16, 185, 129, 0.45);
            color: #6ee7b7;
            padding: 16px;
            border-radius: var(--radius-lg);
            font-size: 16px;
            font-weight: 700;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .unit-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
            text-align: left;
        }

        .unit-cell {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 14px;
        }

        .unit-cell small {
            display: block;
            color: var(--text-dim);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .unit-cell strong {
            color: #fff;
            font-size: 14px;
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
                <span class="brand-badge" style="background: rgba(239,68,68,0.2); color: #f87171; border-color: rgba(239,68,68,0.4);">SOS ACTIVE</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Return to Dashboard</a>
        </nav>
    </header>

    <main class="container">

        <div class="sos-wrapper">

            <!-- SOS DISPATCH STATUS -->
            <div class="sos-header-card">
                <div style="font-size: 48px; margin-bottom: 10px;">🚑</div>
                <h1 style="font-size: 28px; color: #fff; margin-bottom: 8px;">Emergency Unit Dispatched</h1>
                <p style="color: #fca5a5; font-size: 15px;">Your GPS telemetry and medical profile have been transmitted to the paramedic dispatch center.</p>

                <div class="eta-tracker">
                    <span>📍 Live Telemetry: Ambulance unit is ~4-6 minutes away</span>
                </div>

                <div class="unit-details-grid">
                    <div class="unit-cell">
                        <small>Lead Paramedic</small>
                        <strong><?php echo htmlspecialchars($driver_name); ?></strong>
                    </div>
                    <div class="unit-cell">
                        <small>Ambulance Call Sign</small>
                        <strong><?php echo htmlspecialchars($ambulance_no); ?></strong>
                    </div>
                    <div class="unit-cell">
                        <small>Receiving Facility</small>
                        <strong><?php echo htmlspecialchars($hospital_name); ?></strong>
                    </div>
                </div>
            </div>

            <!-- FAMILY CONTACTS BROADCAST -->
            <div class="glass-panel" style="text-align: left;">
                <h3 style="font-size: 18px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    📲 Automated Emergency Circle Broadcast
                </h3>
                <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 18px;">
                    An SMS notification with your live tracking route has been broadcast to your registered emergency contacts:
                </p>

                <?php if ($family && $family->num_rows > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php while($f = $family->fetch_assoc()): ?>
                            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); padding: 12px 18px; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($f['name']); ?></strong>
                                    <span style="color: var(--text-dim); font-size: 12px; margin-left: 6px;">(<?php echo htmlspecialchars($f['relation']); ?>)</span>
                                </div>
                                <span class="badge badge-success">✓ Notified: <?php echo htmlspecialchars($f['phone']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; padding: 14px; border-radius: var(--radius-md); font-size: 13px;">
                        ⚠️ No emergency contacts registered yet. You can configure them in your <a href="family.php" style="color: #fff; text-decoration: underline;">Family Circle</a>.
                    </div>
                <?php endif; ?>

                <div style="margin-top: 25px; text-align: center;">
                    <a href="dashboard.php" class="btn btn-primary">
                        Return to Patient Dashboard 📊
                    </a>
                </div>
            </div>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; 24/7 Rapid Emergency Response Network
    </footer>

</div>

</body>
</html>