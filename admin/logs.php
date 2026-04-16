<?php
/**
 * ADMIN ACTIVITY LOGS
 * Biblioteca Obscura - Action Audit Trail
 */

session_start();

if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: login.php');
    exit;
}

require_once '../db/config.php';

// Database connection is already established in config.php
// $db variable is available and ready to use

// Pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$total = $db->query("SELECT COUNT(*) as cnt FROM admin_logs")->fetch(PDO::FETCH_ASSOC)['cnt'];
$pages = ceil($total / $per_page);

// Get logs
$logs = $db->query("
    SELECT * FROM admin_logs
    ORDER BY created_at DESC
    LIMIT $per_page OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);

// Group logs by action type
$action_types = [
    'add_book' => '✦ Added Book',
    'edit_book' => '◊ Edited Book',
    'delete_book' => '✗ Deleted Book',
    'add_category' => '☷ Added Category',
    'edit_category' => '◊ Edited Category',
    'delete_category' => '✗ Deleted Category'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Biblioteca Obscura</title>
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
            max-width: 1000px;
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

        .header-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            background: rgba(194, 163, 93, 0.2);
            color: #C2A35D;
            border: 1px solid #C2A35D;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: rgba(194, 163, 93, 0.3);
            transform: translateY(-2px);
        }

        /* Timeline */
        .timeline-container {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 30px;
        }

        .timeline {
            position: relative;
            padding-left: 40px;
        }

        .timeline::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #C2A35D 0%, transparent 100%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(194, 163, 93, 0.2);
        }

        .timeline-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -28px;
            top: 0;
            width: 18px;
            height: 18px;
            background: #1C1B1A;
            border: 2px solid #C2A35D;
            border-radius: 50%;
        }

        .timeline-item:hover .timeline-dot {
            background: #C2A35D;
        }

        .log-action {
            font-size: 0.9rem;
            color: #C2A35D;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .log-details {
            color: #E2D3B7;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .log-time {
            color: #8B7D75;
            font-size: 0.85rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #8B7D75;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #C2A35D;
            background: rgba(194, 163, 93, 0.1);
            color: #C2A35D;
            text-decoration: none;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .pagination a:hover {
            background: rgba(194, 163, 93, 0.2);
        }

        .pagination span.current {
            background: #C2A35D;
            color: #1C1B1A;
            font-weight: 500;
        }

        .pagination span.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .timeline {
                padding-left: 30px;
            }

            .timeline-dot {
                left: -22px;
            }

            .timeline-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Activity Logs</h1>
            <a href="dashboard.php" class="btn">← Dashboard</a>
        </div>

        <!-- Timeline -->
        <div class="timeline-container">
            <?php if (count($logs) > 0): ?>
                <div class="timeline">
                    <?php foreach ($logs as $log): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="log-action">
                                <?= $action_types[$log['action']] ?? htmlspecialchars($log['action']) ?>
                            </div>
                            <div class="log-details">
                                <?= htmlspecialchars($log['details']) ?>
                            </div>
                            <div class="log-time">
                                <?= date('F d, Y · g:i A', strtotime($log['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="logs.php?page=1">← First</a>
                            <a href="logs.php?page=<?= $page - 1 ?>">← Previous</a>
                        <?php else: ?>
                            <span class="disabled">← First</span>
                            <span class="disabled">← Previous</span>
                        <?php endif; ?>

                        <span>Page <span class="current"><?= $page ?></span> of <?= $pages ?></span>

                        <?php if ($page < $pages): ?>
                            <a href="logs.php?page=<?= $page + 1 ?>">Next →</a>
                            <a href="logs.php?page=<?= $pages ?>">Last →</a>
                        <?php else: ?>
                            <span class="disabled">Next →</span>
                            <span class="disabled">Last →</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">⟐</div>
                    <div>No activity recorded yet</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
