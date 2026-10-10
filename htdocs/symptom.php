<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = (int)$_SESSION['user_id'];
$result_text = "";
$department = "";
$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $symptoms_raw = strtolower(trim($_POST['symptoms'] ?? ''));
    $duration = $_POST['duration'] ?? '1-2 days';
    $severity = $_POST['severity'] ?? 'Mild';

    $words = preg_split('/[\s,]+/', $symptoms_raw);

    $fever = ['fever','temperature','chills'];
    $cold = ['cough','cold','sneezing','throat','flu'];
    $heart = ['chest','pain','pressure','tightness','heart'];
    $brain = ['headache','vomiting','nausea','dizziness','migraine'];
    $stomach = ['stomach','pain','gas','acidity','diarrhea','cramp'];

    $result_text = "General Clinical Consultation Recommended";
    $department = "General Medicine";

    if (count(array_intersect($words, $fever)) > 0 && count(array_intersect($words, $cold)) > 0) {
        $result_text = "Potential Viral Infection / Upper Respiratory Flu";
        $department = "General Medicine";
    } elseif (count(array_intersect($words, $heart)) >= 2) {
        $result_text = "Cardiac Discomfort / Potential Cardiovascular Warning";
        $department = "Cardiology";
        if ($severity === "Severe") {
            $alert = "🚨 HIGH PRIORITY WARNING: Severe chest pain detected. Please seek immediate emergency medical care or call an ambulance!";
        }
    } elseif (count(array_intersect($words, $brain)) >= 2) {
        $result_text = "Neurological / Migraine / Tension Episode";
        $department = "Neurology";
    } elseif (count(array_intersect($words, $stomach)) >= 1) {
        $result_text = "Gastrointestinal / Digestive Distress";
        $department = "Gastroenterology";
    } elseif (count(array_intersect($words, $fever)) >= 1) {
        $result_text = "Elevated Temperature / Febrile Symptoms";
        $department = "General Medicine";
    }

    if ($duration === "1 week+" && empty($alert)) {
        $alert = "⚠️ Symptoms persistent for over a week. Comprehensive clinical review strongly advised.";
    }

    $stmt = $conn->prepare("
        INSERT INTO symptom_checks 
        (user_id, symptoms, duration, severity, possible_issue, suggested_department)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("isssss", $user_id, $symptoms_raw, $duration, $severity, $result_text, $department);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - AI Symptom Checker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checker-layout {
            max-width: 680px;
            margin: 0 auto;
        }

        .chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 14px;
        }

        .symptom-chip {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-full);
            padding: 6px 14px;
            font-size: 12px;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
        }

        .symptom-chip:hover {
            border-color: var(--primary-light);
            color: #fff;
            background: rgba(14, 165, 233, 0.15);
        }

        .triage-result-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: var(--radius-lg);
            padding: 26px;
            margin-top: 30px;
            animation: fadeIn 0.4s ease;
        }

        .triage-alert {
            background: rgba(239, 68, 68, 0.18);
            border: 1px solid rgba(239, 68, 68, 0.45);
            color: #fca5a5;
            padding: 14px;
            border-radius: var(--radius-md);
            margin-top: 15px;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
    <script>
        function addChip(text) {
            const textarea = document.getElementById("symptoms-field");
            if (textarea.value.trim() === "") {
                textarea.value = text;
            } else {
                textarea.value += ", " + text;
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
                <span class="brand-badge">AI TRIAGE</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <main class="container">

        <div class="checker-layout">

            <div style="text-align: center; margin-bottom: 25px;">
                <h2>🧠 Smart Symptom Checker</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Algorithmic triage engine assessing symptom patterns & routing you to the right department.</p>
            </div>

            <div class="glass-panel">

                <form method="POST">

                    <div class="form-group">
                        <label class="form-label">Describe your current symptoms</label>
                        <textarea id="symptoms-field" name="symptoms" rows="3" class="form-control" placeholder="e.g. fever, headache, dry cough, dizziness" required><?php echo htmlspecialchars($_POST['symptoms'] ?? ''); ?></textarea>
                        
                        <div class="chips-container">
                            <span class="symptom-chip" onclick="addChip('fever')">+ Fever</span>
                            <span class="symptom-chip" onclick="addChip('cough')">+ Cough</span>
                            <span class="symptom-chip" onclick="addChip('headache')">+ Headache</span>
                            <span class="symptom-chip" onclick="addChip('chest pain')">+ Chest Pain</span>
                            <span class="symptom-chip" onclick="addChip('stomach pain')">+ Stomach Pain</span>
                            <span class="symptom-chip" onclick="addChip('fatigue')">+ Fatigue</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Symptom Duration</label>
                            <select name="duration" class="form-control">
                                <option value="1-2 days">1 - 2 days</option>
                                <option value="3-5 days">3 - 5 days</option>
                                <option value="1 week+">More than 1 week</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Severity Level</label>
                            <select name="severity" class="form-control">
                                <option value="Mild">Mild (Noticeable)</option>
                                <option value="Moderate">Moderate (Distracting)</option>
                                <option value="Severe">Severe (Debilitating)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top: 10px;">
                        Analyze Symptoms & Get Recommendations 🔍
                    </button>

                </form>

                <?php if (!empty($result_text)): ?>
                    <div class="triage-result-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="badge badge-primary">Triage Assessment</span>
                            <span class="badge badge-success">Analyzed</span>
                        </div>

                        <h3 style="font-size: 22px; margin-bottom: 8px;"><?php echo htmlspecialchars($result_text); ?></h3>
                        
                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 15px;">
                            Suggested Clinical Department: <strong style="color: var(--primary-light);"><?php echo htmlspecialchars($department); ?></strong>
                        </p>

                        <?php if (!empty($alert)): ?>
                            <div class="triage-alert">
                                <?php echo htmlspecialchars($alert); ?>
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap;">
                            <a href="appointment.php" class="btn btn-primary">
                                📅 Book Appointment in <?php echo htmlspecialchars($department); ?>
                            </a>
                            <a href="medicine.php" class="btn btn-secondary">
                                💊 View Related Medicines
                            </a>
                        </div>

                        <div style="margin-top: 18px; font-size: 12px; color: var(--text-dim); text-align: center;">
                            ℹ️ Note: This tool provides educational guidance only and does not replace a professional clinical diagnosis.
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </main>

    <footer class="med-footer">
        MediVerse &bull; Smart Algorithmic Medical Triage
    </footer>

</div>

</body>
</html>