<?php
/**
 * ADMIN DASHBOARD
 * Biblioteca Obscura - Main Admin Interface
 */

session_start();

// Check if authenticated
if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: login.php');
    exit;
}

// Include database config
require_once '../db/config.php';

// Database connection is already made in config.php!
// $db variable is available and ready to use

// Get admin statistics
$stats = getAdminStats($db);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Biblioteca Obscura</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Cormorant Garamond', serif;
            background: linear-gradient(135deg, #1C1B1A 0%, #2A2926 50%, #1C1B1A 100%);
            color: #E2D3B7;
        }

        body {
            padding: 0;
            margin: 0;
        }

        /* Main layout */
        .admin-wrapper {
            display: flex;
            height: 100vh;
            background: #1C1B1A;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2A2926, #1C1B1A);
            border-right: 2px solid #C2A35D;
            padding: 30px 20px;
            overflow-y: auto;
            position: relative;
        }

        .admin-sidebar::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #C2A35D, transparent);
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #C2A35D;
        }

        .sidebar-logo {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .sidebar-title {
            font-size: 1.2rem;
            color: #C2A35D;
            letter-spacing: 2px;
            font-weight: 300;
        }

        .sidebar-subtitle {
            font-size: 0.75rem;
            color: #8B7D75;
            letter-spacing: 1px;
            margin-top: 5px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-item {
            padding: 15px;
            text-decoration: none;
            color: #E2D3B7;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .nav-item:hover {
            background: rgba(194, 163, 93, 0.1);
            border-left-color: #C2A35D;
            padding-left: 20px;
        }

        .nav-item.active {
            background: rgba(194, 163, 93, 0.15);
            border-left-color: #C2A35D;
            color: #C2A35D;
        }

        .nav-icon {
            font-size: 1.3rem;
            width: 25px;
            text-align: center;
        }

        /* Main content */
        .admin-content {
            flex: 1;
            overflow-y: auto;
            padding: 40px;
            position: relative;
        }

        .admin-content::before {
            content: "";
            position: fixed;
            inset: 0;
            background: radial-gradient(
                circle at 50% 30%,
                rgba(194, 163, 93, 0.05),
                transparent 70%
            );
            pointer-events: none;
        }

        /* Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 10;
        }

        .page-title {
            font-size: 2.5rem;
            color: #C2A35D;
            letter-spacing: 3px;
            font-weight: 300;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-status {
            text-align: right;
            font-size: 0.9rem;
        }

        .user-status span {
            color: #C2A35D;
            font-weight: 500;
        }

        .logout-btn {
            padding: 10px 20px;
            background: rgba(194, 163, 93, 0.2);
            border: 1px solid #C2A35D;
            color: #C2A35D;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(194, 163, 93, 0.3);
            box-shadow: 0 0 20px rgba(194, 163, 93, 0.3);
        }

        /* Dashboard grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            position: relative;
            z-index: 10;
        }

        /* Stat card */
        .stat-card {
            background: rgba(60, 47, 47, 0.6);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(194, 163, 93, 0.2);
            background: rgba(60, 47, 47, 0.8);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2.2rem;
            color: #C2A35D;
            font-weight: 300;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #8B7D75;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Quick actions */
        .quick-actions {
            background: rgba(60, 47, 47, 0.6);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            z-index: 10;
        }

        .quick-actions-title {
            font-size: 1.4rem;
            color: #C2A35D;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-button {
            padding: 20px;
            background: linear-gradient(135deg, rgba(194, 163, 93, 0.1), rgba(139, 125, 117, 0.1));
            border: 1px solid #C2A35D;
            color: #C2A35D;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
            font-size: 1rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .action-button:hover {
            background: linear-gradient(135deg, rgba(194, 163, 93, 0.2), rgba(139, 125, 117, 0.15));
            box-shadow: 0 0 20px rgba(194, 163, 93, 0.3);
            transform: translateY(-3px);
        }

        .action-icon {
            font-size: 2rem;
        }

        /* Recent activity */
        .recent-activity {
            background: rgba(60, 47, 47, 0.6);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 25px;
            position: relative;
            z-index: 10;
        }

        .activity-title {
            font-size: 1.4rem;
            color: #C2A35D;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .activity-item {
            padding: 12px;
            background: rgba(28, 27, 26, 0.5);
            border-left: 3px solid #C2A35D;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .activity-time {
            color: #8B7D75;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .activity-empty {
            text-align: center;
            color: #8B7D75;
            padding: 20px;
            font-style: italic;
        }

        /* Divider */
        .divider {
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #C2A35D, transparent);
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .admin-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid #C2A35D;
                padding: 20px;
            }

            .admin-content {
                padding: 20px;
            }

            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">◆</div>
                <div class="sidebar-title">Admin Panel</div>
                <div class="sidebar-subtitle">Archive Management</div>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="nav-icon">◉</span>
                    Dashboard
                </a>
                <a href="books.php" class="nav-item">
                    <span class="nav-icon">◊</span>
                    Manage Books
                </a>
                <a href="categories.php" class="nav-item">
                    <span class="nav-icon">☷</span>
                    Categories
                </a>
                <a href="analytics.php" class="nav-item">
                    <span class="nav-icon">★</span>
                    Analytics
                </a>
                <a href="logs.php" class="nav-item">
                    <span class="nav-icon">⟐</span>
                    Activity Logs
                </a>
                <a href="backup.php" class="nav-item">
                    <span class="nav-icon">✦</span>
                    Backup
                </a>
            </nav>

            <div class="divider" style="margin-top: 40px;"></div>

            <a href="?action=logout" class="nav-item" style="margin-top: 30px; justify-content: center; background: rgba(255, 70, 70, 0.1); border-left-color: transparent;">
                <span class="nav-icon">➜</span>
                Logout
            </a>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="content-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-info">
                    <div class="user-status">
                        Connected as <span>Keeper</span>
                    </div>
                    <button class="logout-btn" onclick="location.href='?action=logout'">Logout</button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="dashboard-grid">
                <div class="stat-card" onclick="location.href='books.php'">
                    <div class="stat-icon">◊</div>
                    <div class="stat-value"><?= $stats['total_books'] ?></div>
                    <div class="stat-label">Total Books</div>
                </div>

                <div class="stat-card" onclick="location.href='categories.php'">
                    <div class="stat-icon">☷</div>
                    <div class="stat-value"><?= $stats['total_categories'] ?></div>
                    <div class="stat-label">Categories</div>
                </div>

                <div class="stat-card" onclick="location.href='analytics.php'">
                    <div class="stat-icon">★</div>
                    <div class="stat-value"><?= $stats['avg_rating'] ?></div>
                    <div class="stat-label">Avg. Rating</div>
                </div>

                <div class="stat-card" onclick="location.href='logs.php'">
                    <div class="stat-icon">⟐</div>
                    <div class="stat-value"><?= $stats['recent_actions'] ?></div>
                    <div class="stat-label">Recent Actions</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="quick-actions-title">Quick Actions</div>
                <div class="actions-grid">
                    <a href="books.php?action=add" class="action-button">
                        <span class="action-icon">✦</span>
                        Add New Book
                    </a>
                    <a href="categories.php?action=add" class="action-button">
                        <span class="action-icon">☷</span>
                        New Category
                    </a>
                    <a href="analytics.php" class="action-button">
                        <span class="action-icon">★</span>
                        View Analytics
                    </a>
                    <a href="backup.php" class="action-button">
                        <span class="action-icon">✓</span>
                        Backup Archive
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="activity-title">Recent Activity</div>
                <div class="activity-list">
                    <?php
                    // Get recent admin actions
                    $recent = $db->query("
                        SELECT action, details, created_at 
                        FROM admin_logs 
                        ORDER BY created_at DESC 
                        LIMIT 8
                    ")->fetchAll(PDO::FETCH_ASSOC);

                    if (count($recent) > 0) {
                        foreach ($recent as $log) {
                            $time = date('M d, Y H:i', strtotime($log['created_at']));
                            $action = htmlspecialchars($log['action']);
                            echo "<div class='activity-item'>
                                <strong>$action</strong>: {$log['details']}
                                <div class='activity-time'>$time</div>
                            </div>";
                        }
                    } else {
                        echo "<div class='activity-empty'>No activity recorded yet</div>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
