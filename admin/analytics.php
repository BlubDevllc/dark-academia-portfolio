<?php
/**
 * ANALYTICS DASHBOARD
 * Biblioteca Obscura - Archive Statistics
 */

session_start();

if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: login.php');
    exit;
}

require_once '../db/config.php';

// Database connection is already established in config.php
// $db variable is available and ready to use

// Get analytics data
$stats = getAdminStats($db);

// Books by rarity
$rarity_dist = $db->query("
    SELECT rarity, COUNT(*) as count
    FROM books
    GROUP BY rarity
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Books by category
$category_dist = $db->query("
    SELECT c.name, COUNT(b.id) as count
    FROM categories c
    LEFT JOIN books b ON c.id = b.category_id
    GROUP BY c.id
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Top rated books
$top_rated = $db->query("
    SELECT title, author, rating
    FROM books
    ORDER BY rating DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent additions
$recent = $db->query("
    SELECT title, author, created_at
    FROM books
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Language distribution
$languages = $db->query("
    SELECT language, COUNT(*) as count
    FROM books
    GROUP BY language
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for charts
$rarity_labels = json_encode(array_column($rarity_dist, 'rarity'));
$rarity_data = json_encode(array_column($rarity_dist, 'count'));

$category_labels = json_encode(array_column($category_dist, 'name'));
$category_data = json_encode(array_column($category_dist, 'count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Biblioteca Obscura</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cormorant Garamond', serif;
            background: linear-gradient(135deg, #1C1B1A 0%, #2A2926 50%, #1C1B1A 100%);
            color: #E2D3B7;
            padding: 40px 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 2.5rem;
            color: #C2A35D;
            letter-spacing: 3px;
        }

        .back-link {
            padding: 12px 25px;
            background: rgba(194, 163, 93, 0.2);
            color: #C2A35D;
            border: 1px solid #C2A35D;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(194, 163, 93, 0.3);
            transform: translateY(-2px);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 25px;
            text-align: center;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2rem;
            color: #C2A35D;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #8B7D75;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .chart-container {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 25px;
            position: relative;
        }

        .chart-title {
            font-size: 1.3rem;
            color: #C2A35D;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-align: center;
        }

        canvas {
            max-height: 300px;
        }

        /* Lists */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .list-container {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 25px;
        }

        .list-title {
            font-size: 1.3rem;
            color: #C2A35D;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-align: center;
        }

        .list-item {
            padding: 12px;
            margin-bottom: 10px;
            background: rgba(28, 27, 26, 0.5);
            border-left: 3px solid #C2A35D;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-item:last-child {
            margin-bottom: 0;
        }

        .list-item-title {
            flex: 1;
        }

        .list-item-meta {
            color: #8B7D75;
            font-size: 0.9rem;
            margin-left: 10px;
            min-width: 60px;
            text-align: right;
        }

        .list-item-subtitle {
            font-size: 0.85rem;
            color: #8B7D75;
            margin-top: 3px;
            font-style: italic;
        }

        .rating {
            color: #C2A35D;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Analytics</h1>
            <a href="dashboard.php" class="back-link">← Dashboard</a>
        </div>

        <!-- Key Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">◊</div>
                <div class="stat-value"><?= $stats['total_books'] ?></div>
                <div class="stat-label">Total Books</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">★</div>
                <div class="stat-value"><?= $stats['avg_rating'] ?></div>
                <div class="stat-label">Average Rating</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⟐</div>
                <div class="stat-value"><?= $stats['recent_actions'] ?></div>
                <div class="stat-label">Recent Actions</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">☷</div>
                <div class="stat-value"><?= $stats['total_categories'] ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <?php if (count($rarity_dist) > 0): ?>
            <div class="chart-container">
                <div class="chart-title">Books by Rarity</div>
                <canvas id="rarityChart"></canvas>
            </div>
            <?php endif; ?>

            <?php if (count($category_dist) > 0): ?>
            <div class="chart-container">
                <div class="chart-title">Books by Category</div>
                <canvas id="categoryChart"></canvas>
            </div>
            <?php endif; ?>

            <?php if (count($languages) > 0): ?>
            <div class="chart-container">
                <div class="chart-title">Languages</div>
                <canvas id="languageChart"></canvas>
            </div>
            <?php endif; ?>
        </div>

        <!-- Top Rated & Recent -->
        <div class="content-grid">
            <div class="list-container">
                <div class="list-title">★ Top Rated</div>
                <?php foreach ($top_rated as $book): ?>
                    <div class="list-item">
                        <div class="list-item-title">
                            <?= htmlspecialchars($book['title']) ?>
                            <div class="list-item-subtitle">by <?= htmlspecialchars($book['author']) ?></div>
                        </div>
                        <div class="list-item-meta rating"><?= $book['rating'] ?>/5</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="list-container">
                <div class="list-title">✦ Recently Added</div>
                <?php foreach ($recent as $book): ?>
                    <div class="list-item">
                        <div class="list-item-title">
                            <?= htmlspecialchars($book['title']) ?>
                            <div class="list-item-subtitle"><?= date('M d, Y', strtotime($book['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($languages) > 0): ?>
            <div class="list-container">
                <div class="list-title">◇ By Language</div>
                <?php foreach ($languages as $lang): ?>
                    <div class="list-item">
                        <div class="list-item-title"><?= htmlspecialchars($lang['language']) ?></div>
                        <div class="list-item-meta"><?= $lang['count'] ?> book<?php echo $lang['count'] !== 1 ? 's' : ''; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($rarity_dist) > 0): ?>
    <script>
        const rarityCtx = document.getElementById('rarityChart');
        if (rarityCtx) {
            new Chart(rarityCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= $rarity_labels ?>,
                    datasets: [{
                        data: <?= $rarity_data ?>,
                        backgroundColor: [
                            'rgba(194, 163, 93, 0.6)',
                            'rgba(100, 200, 100, 0.5)',
                            'rgba(100, 150, 200, 0.5)',
                            'rgba(150, 100, 200, 0.5)'
                        ],
                        borderColor: '#C2A35D',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#C2A35D',
                                font: { family: "'Cormorant Garamond', serif", size: 12 }
                            }
                        }
                    }
                }
            });
        }
    </script>
    <?php endif; ?>

    <?php if (count($category_dist) > 0): ?>
    <script>
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: <?= $category_labels ?>,
                    datasets: [{
                        label: 'Books',
                        data: <?= $category_data ?>,
                        backgroundColor: 'rgba(194, 163, 93, 0.6)',
                        borderColor: '#C2A35D',
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#C2A35D',
                                font: { family: "'Cormorant Garamond', serif" }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#C2A35D' },
                            grid: { color: 'rgba(194, 163, 93, 0.1)' }
                        },
                        y: {
                            ticks: { color: '#C2A35D' },
                            grid: { color: 'rgba(194, 163, 93, 0.1)' }
                        }
                    }
                }
            });
        }
    </script>
    <?php endif; ?>

    <?php if (count($languages) > 0): ?>
    <script>
        const langCtx = document.getElementById('languageChart');
        if (langCtx) {
            new Chart(langCtx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_column($languages, 'language')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($languages, 'count')) ?>,
                        backgroundColor: [
                            'rgba(194, 163, 93, 0.6)',
                            'rgba(139, 125, 117, 0.6)',
                            'rgba(100, 150, 200, 0.6)',
                            'rgba(100, 200, 100, 0.6)',
                            'rgba(200, 100, 100, 0.6)'
                        ],
                        borderColor: '#C2A35D',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#C2A35D',
                                font: { family: "'Cormorant Garamond', serif", size: 12 }
                            }
                        }
                    }
                }
            });
        }
    </script>
    <?php endif; ?>
</body>
</html>
