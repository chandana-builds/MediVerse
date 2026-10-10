<?php
session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - Next-Gen Smart Healthcare Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hero Section */
        .hero {
            padding: 90px 20px 60px;
            text-align: center;
            max-width: 980px;
            margin: 0 auto;
            position: relative;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.35);
            border-radius: var(--radius-full);
            color: var(--primary-light);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            animation: fadeInDown 0.8s ease;
        }

        .hero h1 {
            font-size: clamp(38px, 6vw, 64px);
            line-height: 1.15;
            margin-bottom: 22px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .hero p {
            font-size: clamp(16px, 2vw, 19px);
            color: var(--text-muted);
            max-width: 720px;
            margin: 0 auto 36px;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 50px;
        }

        /* Stats Bar */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 70px;
        }

        .stat-card {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 24px;
            text-align: center;
            backdrop-filter: blur(12px);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.2);
        }

        .stat-number {
            font-family: var(--font-heading);
            font-size: 36px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Services Grid */
        .section-header {
            text-align: center;
            margin-bottom: 45px;
        }

        .section-header h2 {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 80px;
        }

        .service-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 32px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
            backdrop-filter: blur(14px);
        }

        .service-card:hover {
            transform: translateY(-6px);
            background: var(--bg-card-hover);
            border-color: rgba(56, 189, 248, 0.35);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.6);
        }

        .service-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 20px;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.25);
        }

        .service-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
        }

        .service-card p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .service-link {
            color: var(--primary-light);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .service-link:hover {
            color: #fff;
            gap: 10px;
        }

        /* Banner CTA */
        .cta-banner {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(99, 102, 241, 0.2));
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: var(--radius-xl);
            padding: 50px 40px;
            text-align: center;
            margin-bottom: 70px;
            position: relative;
            backdrop-filter: blur(16px);
        }

        .cta-banner h2 {
            font-size: 34px;
            margin-bottom: 14px;
        }

        .cta-banner p {
            color: var(--text-muted);
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto 28px;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
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
                <span class="brand-badge">PRO</span>
            </div>
        </a>

        <nav class="nav-menu">
            <a href="login.php" class="nav-link">Sign In</a>
            <a href="register.php" class="btn btn-primary btn-sm">Get Started</a>
        </nav>
    </header>

    <!-- MAIN CONTENT -->
    <main class="container">

        <!-- HERO -->
        <section class="hero">
            <div class="hero-tag">
                ✨ Unified Digital Health Ecosystem
            </div>

            <h1>
                Intelligent Healthcare, <br>
                <span class="gradient-text">Connected Seamlessly.</span>
            </h1>

            <p>
                MediVerse unifies patient records, smart doctor scheduling, certified e-pharmacy orders, AI symptom analysis, and instant emergency response in one unified platform.
            </p>

            <div class="hero-actions">
                <a href="register.php" class="btn btn-primary btn-lg">Create Free Account 🚀</a>
                <a href="login.php" class="btn btn-secondary btn-lg">Patient Login 🔐</a>
            </div>
        </section>

        <!-- STATS -->
        <section class="stats-strip">
            <div class="stat-card">
                <div class="stat-number gradient-text">24/7</div>
                <div class="stat-label">Emergency SOS Support</div>
            </div>
            <div class="stat-card">
                <div class="stat-number gradient-text">15+</div>
                <div class="stat-label">Specialist Departments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number gradient-text">&lt; 5m</div>
                <div class="stat-label">Ambulance Dispatch Time</div>
            </div>
            <div class="stat-card">
                <div class="stat-number gradient-text">100%</div>
                <div class="stat-label">Secure Health Vault</div>
            </div>
        </section>

        <!-- SERVICES -->
        <section>
            <div class="section-header">
                <h2>Designed for Comprehensive Patient Care</h2>
                <p>Everything you need to manage personal and family health, prescription medications, and emergency situations.</p>
            </div>

            <div class="services-grid">
                <!-- Appointment -->
                <div class="service-card">
                    <div class="service-icon" style="color: #38bdf8;">📅</div>
                    <h3>Doctor Appointments</h3>
                    <p>Filter verified medical specialists across Cardiology, Neurology, and General Medicine with instant time slot confirmation.</p>
                    <a href="login.php" class="service-link">Schedule a Visit →</a>
                </div>

                <!-- Emergency SOS -->
                <div class="service-card">
                    <div class="service-icon" style="background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); color: #f87171;">🚑</div>
                    <h3>One-Touch Emergency SOS</h3>
                    <p>Instantly dispatch nearby ambulances with real-time ETA tracking and automatic broadcast to emergency family contacts.</p>
                    <a href="login.php" class="service-link" style="color: #f87171;">Emergency Response →</a>
                </div>

                <!-- Pharmacy -->
                <div class="service-card">
                    <div class="service-icon" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); color: #34d399;">💊</div>
                    <h3>Verified Pharmacy</h3>
                    <p>Browse authentic medications, upload digital prescriptions, track pending order states, and choose convenient payment methods.</p>
                    <a href="login.php" class="service-link" style="color: #34d399;">Order Medicines →</a>
                </div>

                <!-- AI Symptom Checker -->
                <div class="service-card">
                    <div class="service-icon" style="background: rgba(139, 92, 246, 0.15); border-color: rgba(139, 92, 246, 0.3); color: #a78bfa;">🧠</div>
                    <h3>Symptom Checker</h3>
                    <p>Instant algorithmic symptom triage evaluating severity and duration to map your condition to the correct clinical department.</p>
                    <a href="login.php" class="service-link" style="color: #a78bfa;">Check Symptoms →</a>
                </div>

                <!-- Health Records -->
                <div class="service-card">
                    <div class="service-icon" style="background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); color: #fbbf24;">📂</div>
                    <h3>Encrypted Health Vault</h3>
                    <p>Upload and safely archive medical lab results, doctor consultation summaries, and diagnostic scans accessible anywhere.</p>
                    <a href="login.php" class="service-link" style="color: #fbbf24;">View Vault →</a>
                </div>

                <!-- Family & Insurance -->
                <div class="service-card">
                    <div class="service-icon" style="background: rgba(236, 72, 153, 0.15); border-color: rgba(236, 72, 153, 0.3); color: #f472b6;">👨‍👩‍👧</div>
                    <h3>Family & Insurance</h3>
                    <p>Manage family emergency circles and monitor insurance policy coverage, validity expiration alerts, and claim estimates.</p>
                    <a href="login.php" class="service-link" style="color: #f472b6;">Manage Circle →</a>
                </div>
            </div>
        </section>

        <!-- CTA BANNER -->
        <section class="cta-banner">
            <h2>Take Control of Your Healthcare Today</h2>
            <p>Join patients and families relying on MediVerse for streamlined medical appointments, medicine delivery, and rapid emergency response.</p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <a href="register.php" class="btn btn-primary btn-lg">Create Your Account</a>
                <a href="login.php" class="btn btn-secondary btn-lg">Sign In</a>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="med-footer">
        <div style="margin-bottom: 10px;">
            <strong style="color: #e2e8f0;">MediVerse</strong> &bull; Integrated Smart Healthcare Platform &bull; HIPAA Compliant Architecture
        </div>
        <div>
            &copy; <?php echo date("Y"); ?> MediVerse Technologies. All rights reserved.
        </div>
    </footer>

</div>

</body>
</html>