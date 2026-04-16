<?php
/**
 * BACKUP & EXPORT SYSTEM
 * Biblioteca Obscura - Archive Backup Management
 */

session_start();

if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: login.php');
    exit;
}

require_once '../db/config.php';

// Database connection is already established in config.php
// $db variable is available and ready to use

$message = '';
$error = '';

// Handle export requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $export_type = $_POST['export_type'] ?? '';

    if ($export_type === 'books_json') {
        try {
            $books = getAllBooks($db);
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="books_' . date('Y-m-d_Hi') . '.json"');
            echo json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            $error = "Error exporting books: " . $e->getMessage();
        }
    }

    if ($export_type === 'books_csv') {
        try {
            $books = getAllBooks($db);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="books_' . date('Y-m-d_Hi') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Headers
            fputcsv($output, ['ID', 'Title', 'Author', 'Category', 'Rarity', 'Rating', 'Pages', 'ISBN', 'Year', 'Language', 'Added']);
            
            // Data
            foreach ($books as $book) {
                fputcsv($output, [
                    $book['id'],
                    $book['title'],
                    $book['author'],
                    $book['category_name'] ?? '',
                    $book['rarity'],
                    $book['rating'],
                    $book['pages'],
                    $book['isbn'],
                    $book['published_year'],
                    $book['language'],
                    date('Y-m-d', strtotime($book['created_at']))
                ]);
            }
            
            fclose($output);
            exit;
        } catch (Exception $e) {
            $error = "Error exporting to CSV: " . $e->getMessage();
        }
    }

    if ($export_type === 'database_backup') {
        try {
            $db_file = __DIR__ . '/../db/biblioteca.db';
            
            if (!file_exists($db_file)) {
                throw new Exception('Database file not found');
            }
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="biblioteca_backup_' . date('Y-m-d_Hi') . '.db"');
            readfile($db_file);
            exit;
        } catch (Exception $e) {
            $error = "Error backing up database: " . $e->getMessage();
        }
    }

    if ($export_type === 'full_archive') {
        try {
            $archive_data = [
                'export_date' => date('Y-m-d H:i:s'),
                'books' => getAllBooks($db),
                'categories' => getAllCategories($db),
                'stats' => getAdminStats($db),
                'version' => '1.0'
            ];
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="biblioteca_archive_' . date('Y-m-d_Hi') . '.json"');
            echo json_encode($archive_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            $error = "Error creating archive: " . $e->getMessage();
        }
    }
}

// Get database info
$db_file = __DIR__ . '/../db/biblioteca.db';
$db_size = file_exists($db_file) ? filesize($db_file) : 0;
$db_size_mb = round($db_size / 1024 / 1024, 2);

$book_count = $db->query("SELECT COUNT(*) as cnt FROM books")->fetch(PDO::FETCH_ASSOC)['cnt'];
$cat_count = $db->query("SELECT COUNT(*) as cnt FROM categories")->fetch(PDO::FETCH_ASSOC)['cnt'];
$log_count = $db->query("SELECT COUNT(*) as cnt FROM admin_logs")->fetch(PDO::FETCH_ASSOC)['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Export - Biblioteca Obscura</title>
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

        /* Messages */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: rgba(255, 70, 70, 0.1);
            border-left: 3px solid #ff4646;
            color: #ff9999;
        }

        /* Section */
        .section {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #C2A35D;
            letter-spacing: 2px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            font-size: 1.8rem;
        }

        /* Database Info */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-card {
            background: rgba(28, 27, 26, 0.5);
            border: 1px solid #C2A35D;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
        }

        .info-label {
            color: #8B7D75;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.8rem;
            color: #C2A35D;
        }

        /* Export options */
        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .export-card {
            background: linear-gradient(135deg, rgba(194, 163, 93, 0.1), rgba(139, 125, 117, 0.1));
            border: 1px solid #C2A35D;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .export-card:hover {
            background: linear-gradient(135deg, rgba(194, 163, 93, 0.2), rgba(139, 125, 117, 0.15));
            transform: translateY(-3px);
        }

        .export-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .export-title {
            font-size: 1.1rem;
            color: #C2A35D;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .export-description {
            font-size: 0.85rem;
            color: #8B7D75;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .export-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #C2A35D, #8B7D75);
            color: #1C1B1A;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .export-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(194, 163, 93, 0.3);
        }

        .export-button:active {
            transform: translateY(0);
        }

        /* Warning box */
        .warning-box {
            background: rgba(255, 150, 50, 0.1);
            border: 1px solid #ffa020;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            color: #ffb060;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .warning-icon {
            display: inline-block;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .section {
                padding: 20px;
            }

            .export-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Backup & Export</h1>
            <a href="dashboard.php" class="back-link">← Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="message">✗ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Database Information -->
        <div class="section">
            <div class="section-title">
                <span class="section-icon">◊</span>
                Archive Information
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Total Books</div>
                    <div class="info-value"><?= $book_count ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Categories</div>
                    <div class="info-value"><?= $cat_count ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Activity Logs</div>
                    <div class="info-value"><?= $log_count ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Database Size</div>
                    <div class="info-value"><?= $db_size_mb ?> MB</div>
                </div>
            </div>

            <div class="warning-box">
                <span class="warning-icon">⚠</span>
                Regular backups are recommended to prevent data loss. Export your archive periodically.
            </div>
        </div>

        <!-- Export Options -->
        <div class="section">
            <div class="section-title">
                <span class="section-icon">✦</span>
                Export Options
            </div>

            <div class="export-grid">
                <!-- Books as JSON -->
                <div class="export-card">
                    <div class="export-icon">✓</div>
                    <div class="export-title">Books (JSON)</div>
                    <div class="export-description">
                        Export all books with metadata in JSON format. Perfect for backups and data transfer.
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="export_type" value="books_json">
                        <button type="submit" class="export-button">Download JSON</button>
                    </form>
                </div>

                <!-- Books as CSV -->
                <div class="export-card">
                    <div class="export-icon">◯</div>
                    <div class="export-title">Books (CSV)</div>
                    <div class="export-description">
                        Export books in CSV format for spreadsheet applications like Excel.
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="export_type" value="books_csv">
                        <button type="submit" class="export-button">Download CSV</button>
                    </form>
                </div>

                <!-- Database Backup -->
                <div class="export-card">
                    <div class="export-icon">☷</div>
                    <div class="export-title">Database Backup</div>
                    <div class="export-description">
                        Complete SQLite database backup. Can be restored to recover entire system.
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="export_type" value="database_backup">
                        <button type="submit" class="export-button">Download Database</button>
                    </form>
                </div>

                <!-- Full Archive -->
                <div class="export-card">
                    <div class="export-icon">★</div>
                    <div class="export-title">Full Archive</div>
                    <div class="export-description">
                        Complete archive export including books, categories, and statistics as JSON.
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="export_type" value="full_archive">
                        <button type="submit" class="export-button">Download Archive</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backup Instructions -->
        <div class="section">
            <div class="section-title">
                <span class="section-icon">⟐</span>
                Backup Instructions
            </div>

            <div style="line-height: 1.8; color: #E2D3B7;">
                <p style="margin-bottom: 15px;">
                    <strong style="color: #C2A35D;">Step 1:</strong> Click "Download Database" to backup your entire SQLite database. 
                    This is the most complete backup option.
                </p>
                <p style="margin-bottom: 15px;">
                    <strong style="color: #C2A35D;">Step 2:</strong> Store the backup file in a secure location, preferably on 
                    an external drive or cloud storage.
                </p>
                <p style="margin-bottom: 15px;">
                    <strong style="color: #C2A35D;">Step 3:</strong> For additional safety, also export your books as JSON or CSV. 
                    This provides an extra layer of data protection.
                </p>
                <p>
                    <strong style="color: #C2A35D;">Step 4:</strong> In case of data loss, replace the damaged database file with 
                    your backup file to restore the system.
                </p>
            </div>

            <div class="warning-box" style="margin-top: 20px;">
                <span class="warning-icon">ℹ</span>
                <strong>Database Location:</strong> <code style="background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 2px;">db/biblioteca.db</code>
            </div>
        </div>
    </div>
</body>
</html>
