<?php
session_start();

include_once __DIR__ . "/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$success_msg = "";
$error_msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospital = trim($_POST['hospital'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $doctor = trim($_POST['doctor'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');

    if (empty($hospital) || empty($department) || empty($doctor) || empty($date) || empty($time)) {
        $error_msg = "Please fill in all appointment fields and choose a time slot.";
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments 
            (user_id, hospital, department, doctor_name, appointment_date, appointment_time)
            VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("isssss", $user_id, $hospital, $department, $doctor, $date, $time);

        if ($stmt->execute()) {
            $success_msg = "Appointment scheduled successfully! You can view it in your dashboard.";
            echo "<script>setTimeout(() => { window.location='dashboard.php'; }, 1600);</script>";
        } else {
            $error_msg = "Database error booking appointment: " . $stmt->error;
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
    <title>MediVerse - Book Doctor Appointment</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .booking-card {
            max-width: 640px;
            margin: 0 auto;
        }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .slot-pill {
            padding: 12px 10px;
            background: #f8fafc;
            border: 1.5px solid var(--border-subtle);
            border-radius: var(--radius-md);
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            color: #334155;
        }

        .slot-pill:hover {
            border-color: var(--primary);
            background: #e0f2fe;
            color: var(--primary);
            transform: translateY(-2px);
        }

        .slot-pill.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border-color: #34d399;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
    </style>
    <script>
        function updateDoctors() {
            const dept = document.getElementById("department").value;
            const doctorSelect = document.getElementById("doctor");
            let options = '<option value="">-- Choose Specialist --</option>';

            if (dept === "General Medicine") {
                options += '<option value="Dr. Ramesh Reddy (MD, Internal Med)">Dr. Ramesh Reddy (MD, Internal Med)</option>';
                options += '<option value="Dr. Sunita Kumar (MBBS, Consultant)">Dr. Sunita Kumar (MBBS, Consultant)</option>';
            } else if (dept === "Cardiology") {
                options += '<option value="Dr. Arvind Sharma (DM, Senior Cardiologist)">Dr. Arvind Sharma (DM, Senior Cardiologist)</option>';
                options += '<option value="Dr. Priya Menon (Interventional Cardiology)">Dr. Priya Menon (Interventional Cardiology)</option>';
            } else if (dept === "Neurology") {
                options += '<option value="Dr. Vikram Mehta (MCh, Neuro Specialist)">Dr. Vikram Mehta (MCh, Neuro Specialist)</option>';
            } else if (dept === "Pediatrics") {
                options += '<option value="Dr. Ananya Roy (DNB, Child Specialist)">Dr. Ananya Roy (DNB, Child Specialist)</option>';
            }
            doctorSelect.innerHTML = options;
        }

        function selectSlot(event, time) {
            document.getElementById("selected_time").value = time;
            document.querySelectorAll(".slot-pill").forEach(s => s.classList.remove("active"));
            if (event && event.currentTarget) {
                event.currentTarget.classList.add("active");
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
                <span class="brand-badge">APPOINTMENTS</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <!-- CONTENT -->
    <main class="container">

        <div class="page-header">
            <div>
                <h2>📅 Book a Specialist Consultation</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Select certified clinic branch, department, and doctor to secure your slot.</p>
            </div>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">← Back to Dashboard</a>
        </div>

        <div class="glass-panel booking-card">

            <?php if (!empty($success_msg)): ?>
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; padding: 14px; border-radius: var(--radius-md); margin-bottom: 20px;">
                    ✅ <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 14px; border-radius: var(--radius-md); margin-bottom: 20px;">
                    ⚠️ <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label class="form-label">1. Select Hospital / Clinic Branch</label>
                    <select name="hospital" class="form-control" required>
                        <option value="">-- Choose Partner Facility --</option>
                        <option value="City Care Multi-Speciality Hospital">City Care Multi-Speciality Hospital</option>
                        <option value="MediLife Clinic & Diagnostics">MediLife Clinic & Diagnostics</option>
                        <option value="Sunrise Super-Speciality Hospital">Sunrise Super-Speciality Hospital</option>
                        <option value="Apollo Healthcare Center">Apollo Healthcare Center</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">2. Clinical Department</label>
                    <select name="department" id="department" class="form-control" onchange="updateDoctors()" required>
                        <option value="">-- Select Department --</option>
                        <option value="General Medicine">General Medicine</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Pediatrics">Pediatrics</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3. Doctor / Specialist</label>
                    <select name="doctor" id="doctor" class="form-control" required>
                        <option value="">Select Department First</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">4. Consultation Date</label>
                    <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">5. Available Time Slots</label>
                    <div class="slots-grid">
                        <div class="slot-pill" onclick="selectSlot(event, '09:00 AM')">09:00 AM</div>
                        <div class="slot-pill" onclick="selectSlot(event, '10:30 AM')">10:30 AM</div>
                        <div class="slot-pill" onclick="selectSlot(event, '12:00 PM')">12:00 PM</div>
                        <div class="slot-pill" onclick="selectSlot(event, '02:00 PM')">02:00 PM</div>
                        <div class="slot-pill" onclick="selectSlot(event, '04:30 PM')">04:30 PM</div>
                        <div class="slot-pill" onclick="selectSlot(event, '06:00 PM')">06:00 PM</div>
                    </div>
                    <input type="hidden" name="time" id="selected_time" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 15px;">
                    Confirm Appointment Booking 📅
                </button>

            </form>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse Appointments &bull; Verified Certified Doctors
    </footer>

</div>

</body>
</html>