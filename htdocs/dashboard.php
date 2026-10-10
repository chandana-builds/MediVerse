<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'User');

// Fetch user stats & data safely
$result = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date DESC");
$records = $conn->query("SELECT * FROM health_records WHERE user_id = $user_id ORDER BY uploaded_at DESC");
$reminders = $conn->query("SELECT * FROM medicine_reminders WHERE user_id = $user_id ORDER BY time_slot ASC");
$orders = $conn->query("SELECT * FROM medicine_orders WHERE user_id = $user_id ORDER BY created_at DESC");

$apt_count = $result ? $result->num_rows : 0;
$rec_count = $records ? $records->num_rows : 0;
$rem_count = $reminders ? $reminders->num_rows : 0;
$ord_count = $orders ? $orders->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Patient Command Center</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Greeting Header */
        .dash-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(99, 102, 241, 0.1));
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: var(--radius-xl);
            padding: 30px 35px;
            backdrop-filter: blur(16px);
        }

        .welcome-text h1 {
            font-size: 32px;
            margin-bottom: 6px;
        }

        .welcome-text p {
            color: var(--text-muted);
            font-size: 15px;
        }

        .dash-date-badge {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-subtle);
            padding: 10px 18px;
            border-radius: var(--radius-full);
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* KPI Quick Counters */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 22px;
            backdrop-filter: blur(14px);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: var(--transition);
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .kpi-info .kpi-num {
            font-size: 28px;
            font-weight: 800;
            font-family: var(--font-heading);
            color: #fff;
            line-height: 1.1;
        }

        .kpi-info .kpi-title {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Emergency Action Banner */
        .emergency-banner {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(185, 28, 28, 0.28));
            border: 1px solid rgba(239, 68, 68, 0.45);
            border-radius: var(--radius-lg);
            padding: 24px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 35px;
            box-shadow: 0 10px 30px -5px rgba(239, 68, 68, 0.25);
        }

        .emergency-text {
            max-width: 600px;
        }

        .emergency-text h3 {
            color: #fca5a5;
            font-size: 20px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .emergency-text p {
            color: #e2e8f0;
            font-size: 14px;
        }

        /* Hub Navigation Grid */
        .hub-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .hub-item {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 20px 15px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            backdrop-filter: blur(14px);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .hub-item:hover {
            transform: translateY(-4px);
            background: var(--bg-card-hover);
            border-color: rgba(56, 189, 248, 0.5);
            box-shadow: 0 12px 25px -5px rgba(14, 165, 233, 0.25);
        }

        .hub-icon {
            font-size: 32px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
        }

        .hub-title {
            font-size: 14px;
            font-weight: 600;
        }

        /* Data Section Panels */
        .data-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(520px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        @media (max-width: 600px) {
            .data-layout {
                grid-template-columns: 1fr;
            }
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .panel-header h2 {
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-hint {
            text-align: center;
            padding: 35px 20px;
            color: var(--text-dim);
            font-size: 14px;
        }
    </style>
    <script>
        function triggerEmergency() {
            if (confirm("🚑 Do you want to immediately dispatch an emergency ambulance to your current location?")) {
                window.location.href = "emergency.php";
            }
        }
    </script>
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
            <div class="nav-user-pill">
                <div class="nav-user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                <span><?php echo $username; ?></span>
            </div>
            <a href="logout.php" class="btn btn-secondary btn-sm" style="color: #f87171; border-color: rgba(239,68,68,0.3);">Sign Out</a>
        </nav>
    </header>

    <!-- MAIN DASHBOARD CONTENT -->
    <main class="container">

        <!-- WELCOME BANNER -->
        <section class="dash-welcome">
            <div class="welcome-text">
                <h1>Welcome back, <span class="gradient-text"><?php echo $username; ?></span> 👋</h1>
                <p>Access your real-time consultations, active prescription schedules, and verified digital medical vault.</p>
            </div>
            <div class="dash-date-badge">
                🗓️ <?php echo date("l, F j, Y"); ?>
            </div>
        </section>

        <!-- KPI COUNTERS -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8;">📅</div>
                <div class="kpi-info">
                    <div class="kpi-num"><?php echo $apt_count; ?></div>
                    <div class="kpi-title">Appointments</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">⏰</div>
                <div class="kpi-info">
                    <div class="kpi-num"><?php echo $rem_count; ?></div>
                    <div class="kpi-title">Medicine Reminders</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">📦</div>
                <div class="kpi-info">
                    <div class="kpi-num"><?php echo $ord_count; ?></div>
                    <div class="kpi-title">Pharmacy Orders</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa;">📂</div>
                <div class="kpi-info">
                    <div class="kpi-num"><?php echo $rec_count; ?></div>
                    <div class="kpi-title">Health Vault Files</div>
                </div>
            </div>
        </section>

        <!-- EMERGENCY SOS CALLOUT -->
        <section class="emergency-banner">
            <div class="emergency-text">
                <h3>🚨 Emergency Medical Dispatch Service</h3>
                <p>One-touch instant SOS notifies nearby dispatch units and broadcasts live tracking updates to your family emergency circle.</p>
            </div>
            <button onclick="triggerEmergency()" class="btn btn-emergency">
                🚑 REQUEST AMBULANCE NOW
            </button>
        </section>

        <!-- QUICK ACTION NAVIGATION HUB -->
        <section class="hub-grid">
            <a href="appointment.php" class="hub-item">
                <div class="hub-icon">📅</div>
                <div class="hub-title">Book Doctor</div>
            </a>
            <a href="medicine.php" class="hub-item">
                <div class="hub-icon">💊</div>
                <div class="hub-title">Buy Medicines</div>
            </a>
            <a href="reminder.php" class="hub-item">
                <div class="hub-icon">⏰</div>
                <div class="hub-title">Add Reminder</div>
            </a>
            <a href="symptom.php" class="hub-item">
                <div class="hub-icon">🧠</div>
                <div class="hub-title">Symptom Checker</div>
            </a>
            <a href="health_records.php" class="hub-item">
                <div class="hub-icon">📂</div>
                <div class="hub-title">Upload Records</div>
            </a>
            <a href="insurance.php" class="hub-item">
                <div class="hub-icon">💳</div>
                <div class="hub-title">Insurance</div>
            </a>
            <a href="family.php" class="hub-item">
                <div class="hub-icon">👨‍👩‍👧</div>
                <div class="hub-title">Family Circle</div>
            </a>
        </section>

        <!-- RECENT DATA PANELS -->
        <div class="data-layout">

            <!-- APPOINTMENTS PANEL -->
            <div class="glass-panel">
                <div class="panel-header">
                    <h2>📅 Appointments (<?php echo $apt_count; ?>)</h2>
                    <a href="appointment.php" class="btn btn-primary btn-sm">+ Book New</a>
                </div>
                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Hospital</th>
                                <th>Department</th>
                                <th>Doctor</th>
                                <th>Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['hospital']); ?></strong></td>
                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['department']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($row['appointment_date']); ?></div>
                                            <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($row['appointment_time']); ?></div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-hint">No scheduled appointments. Click "+ Book New" to reserve a slot.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- REMINDERS PANEL -->
            <div class="glass-panel">
                <div class="panel-header">
                    <h2>⏰ Medication Schedule (<?php echo $rem_count; ?>)</h2>
                    <a href="reminder.php" class="btn btn-primary btn-sm">+ Add Reminder</a>
                </div>
                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Time Slot</th>
                                <th>Clinical Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reminders && $reminders->num_rows > 0): ?>
                                <?php while($row = $reminders->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>💊 <?php echo htmlspecialchars($row['medicine_name']); ?></strong></td>
                                        <td><span class="badge badge-warning">⏰ <?php echo htmlspecialchars($row['time_slot']); ?></span></td>
                                        <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($row['notes'] ?: 'No special notes'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="empty-hint">No medicine reminders configured yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PHARMACY ORDERS PANEL -->
            <div class="glass-panel">
                <div class="panel-header">
                    <h2>📦 Pharmacy Orders (<?php echo $ord_count; ?>)</h2>
                    <a href="medicine.php" class="btn btn-primary btn-sm">Order Meds</a>
                </div>
                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Qty</th>
                                <th>Pharmacy</th>
                                <th>Prescription</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders && $orders->num_rows > 0): ?>
                                <?php while($row = $orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['medicine_name']); ?></strong></td>
                                        <td><?php echo (int)$row['quantity']; ?></td>
                                        <td style="font-size: 13px; color: var(--text-muted);"><?php echo htmlspecialchars($row['store_name']); ?></td>
                                        <td>
                                            <?php if (!empty($row['prescription_file'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['prescription_file']); ?>" target="_blank" class="badge badge-success" style="text-decoration:none;">📄 Verified</a>
                                            <?php else: ?>
                                                <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text-muted);">Standard</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['payment_method']); ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-hint">No orders placed yet. Browse the pharmacy catalog to order.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- HEALTH RECORDS PANEL -->
            <div class="glass-panel">
                <div class="panel-header">
                    <h2>📂 Health Vault Documents (<?php echo $rec_count; ?>)</h2>
                    <a href="health_records.php" class="btn btn-primary btn-sm">+ Upload Doc</a>
                </div>
                <div class="table-responsive">
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Report Title</th>
                                <th>Uploaded Date</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($records && $records->num_rows > 0): ?>
                                <?php while($row = $records->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>📄 <?php echo htmlspecialchars($row['title']); ?></strong></td>
                                        <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($row['uploaded_at']); ?></td>
                                        <td>
                                            <a href="uploads/<?php echo htmlspecialchars($row['file_name']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 4px 10px;">View Report ↗</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="empty-hint">No health records uploaded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <!-- FOOTER -->
    <footer class="med-footer">
        MediVerse Digital Healthcare Ecosystem &bull; Logged in as <strong><?php echo $username; ?></strong>
    </footer>

</div>

</body>
</html>