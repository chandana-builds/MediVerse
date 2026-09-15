<?php
session_start();
include_once __DIR__ . "/db.php";

// Allow viewing if logged in, or if viewing directly
$tables = [
    'users' => '👥 Users',
    'appointments' => '📅 Appointments',
    'medicines' => '💊 Medicines Catalog',
    'medicine_orders' => '📦 Medicine Orders',
    'medicine_reminders' => '⏰ Reminders',
    'health_records' => '📂 Health Records',
    'symptom_checks' => '🧠 Symptom Checks',
    'insurance' => '💳 Insurance',
    'family_members' => '👨‍👩‍👧 Family Contacts',
    'emergency_requests' => '🚑 Emergency SOS'
];

$selected_table = isset($_GET['table']) && array_key_exists($_GET['table'], $tables) ? $_GET['table'] : 'users';

// Fetch table count statistics
$counts = [];
foreach ($tables as $t => $label) {
    try {
        $res = $conn->query("SELECT COUNT(*) as cnt FROM $t");
        $counts[$t] = $res ? $res->fetch_assoc()['cnt'] : 0;
    } catch (Exception $e) {
        $counts[$t] = 0;
    }
}

// Fetch rows from selected table
$rows = [];
$columns = [];
try {
    $data_res = $conn->query("SELECT * FROM $selected_table ORDER BY id DESC LIMIT 100");
    if ($data_res && $data_res->num_rows > 0) {
        while ($row = $data_res->fetch_assoc()) {
            if (empty($columns)) {
                $columns = array_keys($row);
            }
            $rows[] = $row;
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$db_engine = ($conn instanceof mysqli) ? "MySQL / MariaDB (" . ($host ?? 'localhost') . ")" : "SQLite (mediverse.sqlite)";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MediVerse - Database Viewer</title>
<style>
body {
    margin: 0;
    font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #0f172a;
    color: #e2e8f0;
    min-height: 100vh;
}

.header {
    background: #1e293b;
    padding: 16px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #334155;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand h1 {
    margin: 0;
    font-size: 22px;
    color: #38bdf8;
}

.badge-engine {
    background: #0369a1;
    color: #e0f2fe;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.nav-links a {
    color: #94a3b8;
    text-decoration: none;
    margin-left: 15px;
    font-size: 14px;
    padding: 8px 14px;
    border-radius: 8px;
    transition: 0.2s;
}

.nav-links a:hover {
    background: #334155;
    color: #fff;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 25px 20px;
}

/* TABLE TABS */
.table-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 25px;
}

.tab-btn {
    background: #1e293b;
    color: #94a3b8;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #334155;
    transition: 0.2s;
}

.tab-btn:hover {
    background: #334155;
    color: #fff;
}

.tab-btn.active {
    background: #0284c7;
    color: #fff;
    border-color: #38bdf8;
}

.tab-btn .badge {
    background: rgba(0,0,0,0.3);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
}

/* DATA CARD */
.card {
    background: #1e293b;
    border-radius: 14px;
    border: 1px solid #334155;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.card-header {
    padding: 18px 24px;
    background: #1e293b;
    border-bottom: 1px solid #334155;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #f1f5f9;
}

.card-body {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

th {
    background: #0f172a;
    color: #94a3b8;
    padding: 12px 18px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #334155;
}

td {
    padding: 12px 18px;
    border-bottom: 1px solid #334155;
    color: #e2e8f0;
}

tr:hover td {
    background: #243248;
}

.empty-state {
    padding: 50px 20px;
    text-align: center;
    color: #64748b;
}

.pass-hash {
    font-family: monospace;
    color: #64748b;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
}

.pill-status {
    background: #065f46;
    color: #6ee7b7;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.pill-pending {
    background: #854d0e;
    color: #fde047;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.link-file {
    color: #38bdf8;
    text-decoration: none;
}
.link-file:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="header">
    <div class="brand">
        <h1>🗄️ MediVerse Database Viewer</h1>
        <span class="badge-engine">Engine: <?php echo htmlspecialchars($db_engine); ?></span>
    </div>
    <div class="nav-links">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="index.php">🏠 Home</a>
        <a href="login.php">🔐 Login</a>
    </div>
</div>

<div class="container">

    <!-- Table selector tabs -->
    <div class="table-tabs">
        <?php foreach ($tables as $t => $label): ?>
            <a href="?table=<?php echo $t; ?>" class="tab-btn <?php echo $selected_table === $t ? 'active' : ''; ?>">
                <?php echo $label; ?>
                <span class="badge"><?php echo $counts[$t]; ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Data display -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Table: <code><?php echo htmlspecialchars($selected_table); ?></code> (<?php echo $counts[$selected_table]; ?> records)</h2>
            <span style="font-size: 12px; color: #64748b;">Showing latest up to 100 rows</span>
        </div>
        <div class="card-body">
            <?php if (!empty($rows)): ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($columns as $col): ?>
                                    <td>
                                        <?php 
                                        $val = $row[$col];
                                        if ($col === 'password') {
                                            echo '<span class="pass-hash" title="' . htmlspecialchars($val) . '">' . htmlspecialchars(substr($val, 0, 20)) . '...</span>';
                                        } elseif (($col === 'status' || $col === 'order_status') && !empty($val)) {
                                            $cls = strtolower($val) === 'pending' ? 'pill-pending' : 'pill-status';
                                            echo '<span class="' . $cls . '">' . htmlspecialchars($val) . '</span>';
                                        } elseif (($col === 'file_name' || $col === 'document' || $col === 'prescription_file') && !empty($val)) {
                                            $url = (str_starts_with($val, 'uploads/') ? '' : 'uploads/') . $val;
                                            echo '<a class="link-file" href="' . htmlspecialchars($url) . '" target="_blank">📄 View File</a>';
                                        } else {
                                            echo htmlspecialchars($val ?? 'NULL');
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No records found in `<?php echo htmlspecialchars($selected_table); ?>`</h3>
                    <p>Records will appear here as users interact with the platform.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>
