<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

$user_id = $_SESSION['user_id'];

$result_text = "";
$department = "";
$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Normalize input
    $symptoms_raw = strtolower(trim($_POST['symptoms']));
    $duration = $_POST['duration'];
    $severity = $_POST['severity'];

    // Split words (handles commas, spaces)
    $words = preg_split('/[\s,]+/', $symptoms_raw);

    // Keyword groups
    $fever = ['fever','temperature','chills'];
    $cold = ['cough','cold','sneezing','throat'];
    $heart = ['chest','pain','pressure','tightness'];
    $brain = ['headache','vomiting','nausea','dizziness'];
    $stomach = ['stomach','pain','gas','acidity','diarrhea'];

    // Default result
    $result_text = "General Checkup Recommended";
    $department = "General Medicine";

    // Logic Engine
    if (
        count(array_intersect($words, $fever)) > 0 &&
        count(array_intersect($words, $cold)) > 0
    ) {
        $result_text = "Possible Viral Fever / Flu";
        $department = "General Medicine";

    } elseif (
        count(array_intersect($words, $heart)) >= 2
    ) {
        $result_text = "Possible Heart Issue";
        $department = "Cardiology";

        if ($severity == "Severe") {
            $alert = "🚨 EMERGENCY: Possible cardiac condition. Seek immediate help!";
        }

    } elseif (
        count(array_intersect($words, $brain)) >= 2
    ) {
        $result_text = "Possible Neurological Issue / Migraine";
        $department = "Neurology";

    } elseif (
        count(array_intersect($words, $stomach)) >= 1
    ) {
        $result_text = "Possible Digestive Issue";
        $department = "Gastroenterology";

    } elseif (
        count(array_intersect($words, $fever)) >= 1
    ) {
        $result_text = "Fever detected. Monitor temperature and hydration.";
        $department = "General Medicine";
    }

    // Duration-based refinement
    if ($duration == "1 week+" && !$alert) {
        $alert = "⚠️ Symptoms lasting more than a week. Doctor consultation recommended.";
    }

    // Save to DB (Prepared Statement)
    $stmt = $conn->prepare("
        INSERT INTO symptom_checks 
        (user_id, symptoms, duration, severity, possible_issue, suggested_department)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssss",
        $user_id,
        $symptoms_raw,
        $duration,
        $severity,
        $result_text,
        $department
    );

    $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Symptom Checker</title>

<style>
body{
    margin:0;
    font-family:'Open Sans';
    background:url("hospital-bg.png") no-repeat center;
    background-size:cover;
    color:white;
}

.overlay{
    background:rgba(0,0,0,0.85);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.box{
    background:rgba(255,255,255,0.1);
    padding:30px;
    border-radius:15px;
    width:380px;
}

h2{
    text-align:center;
}

input, textarea, select{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:none;
    border-radius:6px;
}

button{
    width:100%;
    padding:12px;
    background:#1161ee;
    border:none;
    color:white;
    border-radius:20px;
    cursor:pointer;
    font-size:15px;
}

button:hover{
    background:#0d4fc0;
}

.result{
    margin-top:15px;
    padding:15px;
    background:rgba(0,0,0,0.6);
    border-radius:10px;
}

.alert{
    margin-top:10px;
    padding:10px;
    background:red;
    border-radius:8px;
    font-weight:bold;
}

.back{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#00c6ff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="overlay">

<div class="box">

<h2>🧠 Symptom Checker</h2>

<form method="POST">

<label>Symptoms</label>
<textarea name="symptoms" placeholder="e.g. fever, cough, headache" required></textarea>

<label>Duration</label>
<select name="duration">
<option>1-2 days</option>
<option>3-5 days</option>
<option>1 week+</option>
</select>

<label>Severity</label>
<select name="severity">
<option>Mild</option>
<option>Moderate</option>
<option>Severe</option>
</select>

<button type="submit">Analyze</button>

</form>

<?php if (!empty($result_text)): ?>
<div class="result">

<h3>Result:</h3>
<p><?php echo $result_text; ?></p>

<p><b>Recommended Department:</b> <?php echo $department; ?></p>

<?php if (!empty($alert)): ?>
<div class="alert"><?php echo $alert; ?></div>
<?php endif; ?>

<br>

<a href="appointment.php">
<button>📅 Book Appointment</button>
</a>

<br><br>

<small>⚠️ This is not a medical diagnosis. Always consult a doctor.</small>

</div>
<?php endif; ?>

<a href="dashboard.php" class="back">⬅ Back to Dashboard</a>

</div>

</div>

</body>
</html>