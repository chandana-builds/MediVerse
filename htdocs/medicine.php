<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . "/db.php";

// Fetch medicines
$result = $conn->query("SELECT * FROM medicines ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediVerse - E-Pharmacy Catalog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .catalog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .search-box {
            max-width: 380px;
            width: 100%;
        }

        .med-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
            margin-bottom: 50px;
        }

        .med-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 24px;
            backdrop-filter: blur(14px);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .med-card:hover {
            transform: translateY(-5px);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.5);
            background: var(--bg-card-hover);
        }

        .med-badge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .med-title {
            font-size: 19px;
            margin-bottom: 6px;
            color: #fff;
        }

        .med-store {
            color: var(--text-muted);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 18px;
        }

        .med-price-row {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .med-price {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 800;
            color: #34d399;
        }
    </style>
    <script>
        function filterMeds() {
            const query = document.getElementById("search-input").value.toLowerCase();
            const cards = document.querySelectorAll(".med-card");
            cards.forEach(card => {
                const title = card.getAttribute("data-name").toLowerCase();
                const store = card.getAttribute("data-store").toLowerCase();
                if (title.includes(query) || store.includes(query)) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
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
                <span class="brand-badge">PHARMACY</span>
            </div>
        </a>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back</a>
        </nav>
    </header>

    <main class="container">

        <!-- HEADER & FILTER -->
        <div class="catalog-header">
            <div>
                <h2>💊 Certified E-Pharmacy Catalog</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Direct orders from authorized pharmacy centers with home delivery.</p>
            </div>
            <div class="search-box">
                <input type="text" id="search-input" onkeyup="filterMeds()" class="form-control" placeholder="🔍 Search medicine or pharmacy...">
            </div>
        </div>

        <!-- MEDICINES GRID -->
        <div class="med-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="med-card" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-store="<?php echo htmlspecialchars($row['store_location']); ?>">
                        
                        <div class="med-badge-row">
                            <?php if ($row['requires_prescription']): ?>
                                <span class="badge badge-warning">📄 Prescription Required</span>
                            <?php else: ?>
                                <span class="badge badge-success">🟢 Over-The-Counter</span>
                            <?php endif; ?>
                            <span style="font-size: 12px; color: var(--text-dim);">In Stock</span>
                        </div>

                        <h3 class="med-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="med-store">
                            📍 <?php echo htmlspecialchars($row['store_location']); ?>
                        </div>

                        <div class="med-price-row">
                            <div class="med-price">₹<?php echo number_format($row['price'], 2); ?></div>
                            
                            <?php if ($row['requires_prescription']): ?>
                                <form action="upload_prescription.php" method="GET" style="margin: 0;">
                                    <input type="hidden" name="medicine_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" style="border-color: rgba(245, 158, 11, 0.4); color: #fbbf24;">
                                        Upload Rx & Buy
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="checkout.php" method="GET" style="margin: 0;">
                                    <input type="hidden" name="medicine_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        Buy Now 🛍️
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="glass-panel" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <h3>No medicines available in catalog.</h3>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <footer class="med-footer">
        MediVerse Pharmacy Network &bull; Quality Assured & Verified Medications
    </footer>

</div>

</body>
</html>