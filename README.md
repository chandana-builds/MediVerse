<div align="center">

# 🩺 MediVerse
### Next-Gen Smart Healthcare & Clinical Care Management Platform

[![Live Demo](https://img.shields.io/badge/Live%20Demo-mediverse--kcgx.onrender.com-0284c7?style=for-the-badge&logo=render&logoColor=white)](https://mediverse-kcgx.onrender.com/)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Database](https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white)](schema.sql)
[![License](https://img.shields.io/badge/License-MIT-059669?style=for-the-badge)](LICENSE)

<br />

**[🌐 Visit Live Website Demo](https://mediverse-kcgx.onrender.com/)** &nbsp;&bull;&nbsp; **[📖 Explore Documentation](#-key-features)** &nbsp;&bull;&nbsp; **[🚀 Local Quickstart](#-quickstart--local-setup)**

</div>

---

## 🌟 Live Demo

> 🚀 **The platform is deployed live on Render:**  
> 👉 **[https://mediverse-kcgx.onrender.com/](https://mediverse-kcgx.onrender.com/)**
> 
> *Feel free to register a new patient account or test appointment bookings, prescription orders, and AI triage directly in the cloud environment.*

---

## 📖 Overview

**MediVerse** is an integrated, full-stack digital healthcare web platform crafted to streamline patient consultations, e-pharmacy orders, medical records archiving, algorithmic symptom triage, and rapid emergency ambulance dispatch.

Designed with a **professional, warm clinical aesthetic** inspired by modern healthcare leaders, MediVerse pairs a clean, human-centered UI with robust backend security and universal database portability.

---

## ✨ Key Features

### 📅 Smart Doctor Consultations
- Select verified medical branches across top partner hospitals.
- Dynamic specialist filtering across **Cardiology**, **Neurology**, **Pediatrics**, and **General Medicine**.
- Interactive appointment date picker and time-slot reservation pills with instant scheduling.

### 🚑 One-Touch Emergency SOS Dispatch
- Rapid paramedic dispatch with simulated real-time GPS telemetry and estimated arrival times (ETA).
- Automatic broadcast to the patient's registered family emergency circle via integrated alert hooks.

### 💊 Certified E-Pharmacy & Prescription Orders
- Searchable catalog of authentic medications with live stock and price indicators.
- Seamless dual-flow purchasing: direct **Over-The-Counter (OTC)** checkout or mandatory **Prescription Verification (Rx)** upload (PDF, JPG, PNG).
- Dynamic cart subtotal calculator with Cash on Delivery (COD) and Online Payment options.

### 🧠 Algorithmic Symptom Triage Assistant
- Interactive symptom chip selector (Fever, Cough, Headache, Chest Pain, Stomach Pain, etc.).
- Multi-parameter clinical analysis factoring in duration and severity to route patients to the correct medical department.
- High-priority cardiac warning alerts advising immediate emergency intervention when severe symptoms are detected.

### 📂 Encrypted Health Vault
- Secure diagnostic record archive for lab test summaries, blood work, radiology scans, and doctor prescriptions.
- Direct inline file viewing and download management.

### ⏰ Daily Medication Reminders
- Schedule personalized dosage times and meal-relative clinical notes (e.g., *"1 tablet after breakfast"*).
- Visual timeline schedule directly within the patient dashboard.

### 💳 Insurance Portfolio & 👨‍👩‍👧 Family Circle
- Track policy coverage amounts, expiration dates, warning alerts, and automated 80% claim estimation calculations.
- Register family contacts with emergency relationship tags and 24/7 availability status.

---

## 🛠️ Architecture & Tech Stack

| Layer | Technology | Description |
| :--- | :--- | :--- |
| **Frontend UI** | HTML5, CSS3, Vanilla JS | Custom warm healthcare design system (`style.css`), Google Fonts (*Outfit* & *Plus Jakarta Sans*), responsive grid layout |
| **Backend** | PHP 8.3 | Clean MVC-like modular scripts, session authentication, prepared statements |
| **Database** | SQLite3 & MySQL / MariaDB | Universal DB connector (`htdocs/db.php`) supporting zero-config local SQLite and cloud MySQL |
| **Containerization**| Docker & Apache | Multi-stage production container with dynamic port binding (`docker-entrypoint.sh`) |
| **Cloud Hosting** | Render | Deployed as a high-availability Docker web service with automatic SSL |

---

## 🚀 Quickstart & Local Setup

### Prerequisites
- **PHP 8.0+** (with `pdo_sqlite` and `pdo_mysql` extensions enabled)
- Any modern web browser

### 1-Click Launch (Windows)
Double-click [`run.bat`](run.bat) or execute from the terminal:
```bat
./run.bat
```
*This automatically boots the PHP server with custom `php.ini` extensions and opens `http://localhost:8000` in your browser.*

### Manual Terminal Launch
```bash
# Clone the repository
git clone https://github.com/chandana-builds/MediVerse.git
cd MediVerse

# Start PHP built-in server
php -S localhost:8000 -c php.ini -t htdocs
```
Visit **`http://localhost:8000`** in your browser.

---

## 🌐 Deploy to Render

MediVerse is pre-configured with a production [`Dockerfile`](Dockerfile) and [`render.yaml`](render.yaml) specification:

1. Fork or push your code to GitHub.
2. Sign in to [render.com](https://render.com) and click **New + > Web Service**.
3. Connect your **`MediVerse`** repository.
4. Render will automatically detect the **Docker** environment.
5. Select the **Free** instance type and click **Create Web Service**.
6. Your cloud URL will be live in ~2 minutes!

*(Optional) To connect a persistent cloud MySQL database (e.g., Aiven or TiDB), define `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, and `DB_PORT` in your Render Environment Variables.*

---

## 📂 Project Directory Structure

```text
├── htdocs/
│   ├── style.css              # Unified clinical design system (warm light theme)
│   ├── index.php              # Modern healthcare landing page
│   ├── dashboard.php          # Central patient command center
│   ├── login.php              # Patient authentication & session portal
│   ├── register.php           # New user registration & validation
│   ├── appointment.php        # Doctor appointment scheduling module
│   ├── medicine.php           # E-Pharmacy catalog & instant search filter
│   ├── checkout.php           # Direct OTC checkout & payment processing
│   ├── upload_prescription.php# Prescription document verification upload
│   ├── place_order.php        # Secure prepared-statement order handler
│   ├── symptom.php            # Algorithmic clinical symptom triage tool
│   ├── reminder.php           # Medication reminder scheduler & table
│   ├── health_records.php     # Diagnostic records vault & file upload
│   ├── insurance.php          # Insurance policies & claim estimation
│   ├── family.php             # Emergency contact network manager
│   ├── emergency.php          # Ambulance SOS dispatch & telemetry tracking
│   ├── db.php                 # Universal database abstraction connector
│   ├── logout.php             # Session destruction & sign-out handler
│   ├── uploads/               # Secure uploaded patient files & prescriptions
│   └── *.png                  # Hospital background assets
├── Dockerfile                 # Production container build recipe
├── docker-entrypoint.sh       # Dynamic port binding entrypoint
├── render.yaml                # Render cloud deployment blueprint
├── .dockerignore              # Container build exclusions
├── run.bat                    # 1-Click local Windows launcher
├── schema.sql                 # Complete MySQL schema & seed catalog
├── php.ini                    # Local PHP module & upload configuration
└── README.md                  # Project documentation
```

---

## 🔒 Security & Privacy

- **Password Protection**: User passwords are encrypted using PHP's native `password_hash()` utilizing the secure BCrypt algorithm.
- **SQL Injection Prevention**: All order processing, authentication, appointments, and record lookups use parameterized prepared statements (`$stmt->bind_param()`).
- **Data Privacy**: Database internals and administrative queries are isolated to the backend development environment and never exposed to the public UI.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE) - open and free for educational and commercial development.
