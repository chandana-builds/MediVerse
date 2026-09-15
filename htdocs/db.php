<?php
// MediVerse Universal Database Connector
// Supports MySQL when configured/available, and seamlessly falls back to local SQLite

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'mediverse_db';
$port = getenv('DB_PORT') ?: 3306;

$conn = null;

// 1. Attempt MySQL connection with silence on failure
if (extension_loaded('mysqli') && $host !== 'sql207.infinityfree.com') {
    mysqli_report(MYSQLI_REPORT_OFF);
    try {
        $testConn = @new mysqli($host, $user, $pass, $db, (int)$port);
        if (!$testConn->connect_error) {
            $conn = $testConn;
        }
    } catch (Exception $e) {
        $conn = null;
    }
}

// 2. If MySQL is not connected, use the local SQLite wrapper
if (!$conn) {
    class MediverseResult {
        private $rows;
        private $cursor = 0;
        public $num_rows = 0;

        public function __construct(array $rows) {
            $this->rows = $rows;
            $this->num_rows = count($rows);
            $this->cursor = 0;
        }

        public function fetch_assoc() {
            if ($this->cursor < $this->num_rows) {
                return $this->rows[$this->cursor++];
            }
            return null;
        }

        public function fetch_all($mode = MYSQLI_NUM) {
            return $this->rows;
        }
    }

    class MediverseStmt {
        private $pdo;
        private $stmt;
        private $db;
        private $params = [];
        public $num_rows = 0;
        public $error = '';
        private $resultRows = null;

        public function __construct($pdo, $stmt, $db) {
            $this->pdo = $pdo;
            $this->stmt = $stmt;
            $this->db = $db;
        }

        public function bind_param($types, &...$vars) {
            $this->params = $vars;
            return true;
        }

        public function execute($params = null) {
            if ($params !== null) {
                $this->params = $params;
            }
            $this->resultRows = null;
            $this->num_rows = 0;
            $bound = array_values($this->params);

            try {
                $success = $this->stmt->execute(count($bound) > 0 ? $bound : null);
                if (!$success) {
                    $err = $this->stmt->errorInfo();
                    $this->error = $err[2] ?? 'Statement error';
                    $this->db->error = $this->error;
                    return false;
                }
                $this->db->insert_id = (int)$this->pdo->lastInsertId();
                return true;
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                $this->db->error = $this->error;
                return false;
            }
        }

        public function store_result() {
            try {
                $this->resultRows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->num_rows = count($this->resultRows);
                return true;
            } catch (Exception $e) {
                $this->num_rows = 0;
                return false;
            }
        }

        public function get_result() {
            if ($this->resultRows === null) {
                try {
                    $this->resultRows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $this->resultRows = [];
                }
            }
            $this->num_rows = count($this->resultRows);
            return new MediverseResult($this->resultRows);
        }

        public function close() {
            $this->stmt->closeCursor();
            return true;
        }
    }

    class MediverseDB {
        private $pdo;
        public $connect_error = null;
        public $error = '';
        public $insert_id = 0;

        public function __construct($dbFile) {
            try {
                $this->pdo = new PDO("sqlite:" . $dbFile);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->initializeSchema();
            } catch (Exception $e) {
                $this->connect_error = $e->getMessage();
            }
        }

        private function initializeSchema() {
            $queries = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    email TEXT NOT NULL,
                    password TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS appointments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    hospital TEXT NOT NULL,
                    department TEXT NOT NULL,
                    doctor_name TEXT NOT NULL,
                    appointment_date TEXT NOT NULL,
                    appointment_time TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS health_records (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    file_name TEXT NOT NULL,
                    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS medicine_reminders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    medicine_name TEXT NOT NULL,
                    time_slot TEXT NOT NULL,
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS medicines (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    price REAL NOT NULL,
                    store_location TEXT NOT NULL,
                    requires_prescription INTEGER DEFAULT 0
                )",
                "CREATE TABLE IF NOT EXISTS medicine_orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    medicine_name TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    store_name TEXT NOT NULL,
                    payment_method TEXT NOT NULL,
                    prescription_required INTEGER DEFAULT 0,
                    prescription_file TEXT,
                    order_status TEXT DEFAULT 'Pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS symptom_checks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    symptoms TEXT NOT NULL,
                    duration TEXT NOT NULL,
                    severity TEXT NOT NULL,
                    possible_issue TEXT,
                    suggested_department TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS insurance (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    provider TEXT NOT NULL,
                    policy_number TEXT NOT NULL,
                    coverage_amount REAL NOT NULL,
                    valid_till TEXT NOT NULL,
                    document TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS family_members (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    relation TEXT NOT NULL,
                    phone TEXT NOT NULL,
                    availability TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS emergency_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    driver_name TEXT NOT NULL,
                    ambulance_no TEXT NOT NULL,
                    hospital TEXT NOT NULL,
                    status TEXT DEFAULT 'On the way',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ];

            foreach ($queries as $q) {
                $this->pdo->exec($q);
            }

            // Seed medicines if table is empty
            $count = $this->pdo->query("SELECT COUNT(*) as c FROM medicines")->fetch()['c'] ?? 0;
            if ($count == 0) {
                $meds = [
                    ['Paracetamol 650mg', 30.00, 'City Care Pharmacy', 0],
                    ['Amoxicillin 500mg', 120.00, 'MediLife Chemist', 1],
                    ['Cetirizine 10mg', 45.00, 'Apollo Healthcare', 0],
                    ['Pantoprazole 40mg', 90.00, 'CarePlus Meds', 0],
                    ['Azithromycin 500mg', 150.00, 'Sunrise Pharmacy', 1],
                    ['Cough Syrup 100ml', 85.00, 'Wellness Hub', 0],
                    ['Metformin 500mg', 65.00, 'City Care Pharmacy', 1],
                    ['Vitamin C 500mg', 50.00, 'HealthFirst', 0]
                ];
                $stmt = $this->pdo->prepare("INSERT INTO medicines (name, price, store_location, requires_prescription) VALUES (?, ?, ?, ?)");
                foreach ($meds as $m) {
                    $stmt->execute($m);
                }
            }

            // Seed demo user if no users exist
            $uCount = $this->pdo->query("SELECT COUNT(*) as c FROM users")->fetch()['c'] ?? 0;
            if ($uCount == 0) {
                $demoStmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $demoStmt->execute(['demo', 'demo@mediverse.com', password_hash('password123', PASSWORD_DEFAULT)]);
            }
        }

        public function prepare($sql) {
            $stmt = $this->pdo->prepare($sql);
            if (!$stmt) {
                $err = $this->pdo->errorInfo();
                $this->error = $err[2] ?? 'Prepare error';
                return false;
            }
            return new MediverseStmt($this->pdo, $stmt, $this);
        }

        public function query($sql) {
            $trimmed = strtoupper(trim($sql));
            if (str_starts_with($trimmed, 'SELECT') || str_starts_with($trimmed, 'PRAGMA')) {
                $stmt = $this->pdo->query($sql);
                if (!$stmt) {
                    $err = $this->pdo->errorInfo();
                    $this->error = $err[2] ?? 'Query error';
                    return false;
                }
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return new MediverseResult($rows);
            } else {
                $res = $this->pdo->exec($sql);
                if ($res === false) {
                    $err = $this->pdo->errorInfo();
                    $this->error = $err[2] ?? 'Exec error';
                    return false;
                }
                $this->insert_id = (int)$this->pdo->lastInsertId();
                return true;
            }
        }

        public function close() {
            $this->pdo = null;
            return true;
        }
    }

    $conn = new MediverseDB(__DIR__ . '/mediverse.sqlite');
}
?>