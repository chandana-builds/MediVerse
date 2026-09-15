# MediVerse - Smart Healthcare Platform 🏥

MediVerse is a comprehensive, modern digital healthcare web platform built to connect patients with integrated medical services, consultations, health records, emergency SOS response, and online pharmacy management.

---

## 🌟 Key Features

- **🔐 Secure Authentication**: Patient registration, login, and session-managed routes with BCrypt password hashing.
- **📊 Interactive Dashboard**: Central patient dashboard providing an overview of scheduled appointments, records, reminders, and active pharmacy orders.
- **📅 Doctor Appointment Booking**: Select hospitals, filter departments, dynamically assign doctors, and choose preferred appointment dates and time slots.
- **💊 Online Pharmacy & Prescriptions**: Browse medicines, purchase over-the-counter medications, or upload prescriptions for prescription-required drugs.
- **⏰ Medication Reminders**: Schedule daily medication reminders with dosage times and instructions.
- **📂 Digital Health Records**: Upload, store, and view medical reports and test results securely.
- **🧠 AI / Smart Symptom Checker**: Input symptoms, duration, and severity to receive immediate guidance on potential conditions and recommended medical departments.
- **💳 Health Insurance Management**: Track policies, insurance providers, coverage limits, validity dates, and upload policy documentation.
- **👨‍👩‍👧 Family Emergency Circle**: Maintain emergency contacts with relationship labels, contact details, and availability status.
- **🚑 Instant Emergency SOS Dispatch**: One-click ambulance request with real-time tracking simulation, driver assignment, and emergency contact alerts.

---

## 🚀 Getting Started

### Prerequisites
- **PHP 8.0+** (with `mysqli` and `sqlite3` extensions enabled)
- A web browser (Google Chrome, Microsoft Edge, Firefox, etc.)

### Running Locally (1-Click)
Double-click the [`run.bat`](run.bat) file or run:
```bash
./run.bat
```
This automatically boots the PHP server and opens `http://localhost:8000` in your default browser.

### Manual Launch
Start PHP's built-in server pointing to the `htdocs` directory:
```bash
php -S localhost:8000 -t htdocs
```
Then navigate to [http://localhost:8000](http://localhost:8000).

---

## 🗄️ Database Configuration

MediVerse comes equipped with a **universal database connector** ([`htdocs/db.php`](htdocs/db.php)):
1. **Zero-Configuration Mode (Default)**: Automatically uses a local SQLite database with all tables pre-initialized and seeded with default medicines and a demo account (`demo` / `password123`).
2. **MySQL / MariaDB Mode**: Set environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`) to connect directly to any local or remote MySQL server.
3. **Database Schema**: Full DDL schema and sample data are provided in [`schema.sql`](schema.sql) for import into phpMyAdmin, cPanel, or XAMPP.

---

## 📂 Project Structure

```text
├── htdocs/
│   ├── appointment.php         # Doctor appointment booking
│   ├── checkout.php            # Medicine checkout flow
│   ├── dashboard.php          # Main patient dashboard
│   ├── db.php                 # Universal database connector & adapter
│   ├── emergency.php          # Ambulance SOS dispatch & live tracking
│   ├── family.php             # Family contacts management
│   ├── health_records.php     # Medical report uploads & viewing
│   ├── index.php              # Landing page
│   ├── insurance.php          # Health insurance policy manager
│   ├── login.php              # User login
│   ├── logout.php             # User logout & session destroy
│   ├── medicine.php           # Pharmacy medicine catalog
│   ├── place_order.php        # Medicine order processor
│   ├── register.php           # User account registration
│   ├── reminder.php           # Medication reminder scheduler
│   ├── symptom.php            # Symptom assessment tool
│   ├── upload_prescription.php# Prescription document uploader
│   ├── uploads/               # Uploaded reports & prescriptions
│   └── *.png                  # UI background assets
├── run.bat                    # 1-click Windows launcher
├── schema.sql                 # Complete MySQL schema & seed data
├── php.ini                    # Local PHP configuration
└── README.md                  # Project documentation
```

---

## 📄 License
Open source under the [MIT License](LICENSE).
