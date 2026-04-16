<?php
/**
 * CATEGORY MANAGEMENT
 * Biblioteca Obscura - Book Categories
 */

session_start();

if (!isset($_SESSION['admin_authenticated'])) {
    header('Location: login.php');
    exit;
}

require_once '../db/config.php';

// Database connection is already established in config.php
// $db variable is available and ready to use

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'add') {
        try {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($name)) {
                throw new Exception('Category name is required');
            }

            $stmt = $db->prepare("
                INSERT INTO categories (name, description, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$name, $description]);
            $cat_id = $db->lastInsertId();

            logAdminAction($db, 'add_category', null, "Added category: $name");
            $message = "✓ Category added successfully!";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    if ($action_type === 'edit') {
        try {
            $id = $_POST['category_id'] ?? '';
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($name)) {
                throw new Exception('Category name is required');
            }

            $stmt = $db->prepare("
                UPDATE categories 
                SET name = ?, description = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $id]);

            logAdminAction($db, 'edit_category', null, "Updated category: $name");
            $message = "✓ Category updated successfully!";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    if ($action_type === 'delete') {
        try {
            $id = $_POST['category_id'] ?? '';
            
            $cat = $db->query("SELECT name FROM categories WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            
            // Check if category has books
            $count = $db->query("SELECT COUNT(*) as cnt FROM books WHERE category_id = $id")->fetch(PDO::FETCH_ASSOC);
            
            if ($count['cnt'] > 0) {
                throw new Exception("Cannot delete: {$count['cnt']} books are in this category. Move them first.");
            }

            $db->exec("DELETE FROM categories WHERE id = $id");
            
            logAdminAction($db, 'delete_category', null, "Deleted category: {$cat['name']}");
            $message = "✓ Category deleted successfully!";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get all categories with book counts
$categories = $db->query("
    SELECT c.*, COUNT(b.id) as book_count
    FROM categories c
    LEFT JOIN books b ON c.id = b.category_id
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll(PDO::FETCH_ASSOC);

$category = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $category = $db->query("SELECT * FROM categories WHERE id = {$_GET['id']}")->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Biblioteca Obscura</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header */
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
            background: linear-gradient(135deg, #C2A35D, #8B7D75);
            color: #1C1B1A;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(194, 163, 93, 0.3);
        }

        .btn-secondary {
            background: rgba(194, 163, 93, 0.2);
            color: #C2A35D;
            border: 1px solid #C2A35D;
        }

        /* Messages */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: rgba(100, 200, 100, 0.1);
            border-left: 3px solid #64c864;
            color: #90ff90;
        }

        .error {
            background: rgba(255, 70, 70, 0.1);
            border-left-color: #ff4646;
            color: #ff9999;
        }

        /* Form container */
        .form-container {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            padding: 40px;
            margin-bottom: 30px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-title {
            font-size: 1.8rem;
            color: #C2A35D;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #C2A35D;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(28, 27, 26, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 4px;
            color: #E2D3B7;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        input[type="text"]:focus,
        textarea:focus {
            background: rgba(28, 27, 26, 0.95);
            box-shadow: 0 0 20px rgba(194, 163, 93, 0.3);
            border-color: #E2D3B7;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* Table */
        .table-container {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(28, 27, 26, 0.8);
            border-bottom: 2px solid #C2A35D;
        }

        th {
            padding: 15px;
            text-align: left;
            color: #C2A35D;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 500;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #C2A35D;
            color: #E2D3B7;
        }

        tbody tr:hover {
            background: rgba(194, 163, 93, 0.05);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .category-name {
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        .book-count {
            text-align: center;
            color: #8B7D75;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border: 1px solid #C2A35D;
            background: rgba(194, 163, 93, 0.1);
            color: #C2A35D;
            border-radius: 3px;
            cursor: pointer;
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            background: rgba(194, 163, 93, 0.2);
        }

        .action-btn.delete {
            background: rgba(255, 70, 70, 0.1);
            border-color: #ff4646;
            color: #ff9999;
        }

        .action-btn.delete:hover {
            background: rgba(255, 70, 70, 0.2);
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

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .form-container {
                padding: 20px;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 10px;
            }

            .category-name {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Manage Categories</h1>
            <div class="header-buttons">
                <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
                <?php if ($action === 'list'): ?>
                    <a href="categories.php?action=add" class="btn">+ Add Category</a>
                <?php else: ?>
                    <a href="categories.php" class="btn btn-secondary">View All</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message">✓ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">✗ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="form-container">
                <h2 class="form-title"><?= $action === 'add' ? '✦ Add New Category' : '◊ Edit Category' ?></h2>

                <form method="POST">
                    <input type="hidden" name="action_type" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
                    <?php if ($action === 'edit' && $category): ?>
                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" value="<?= $category['name'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?= $category['description'] ?? '' ?></textarea>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn">✓ <?= $action === 'add' ? 'Add Category' : 'Update Category' ?></button>
                        <a href="categories.php" class="btn btn-secondary">✗ Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Categories List -->
        <?php if ($action === 'list'): ?>
            <?php if (count($categories) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th style="text-align: center; width: 100px;">Books</th>
                                <th style="text-align: right; width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="category-name"><?= htmlspecialchars($cat['name']) ?></td>
                                    <td style="color: #8B7D75;"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 50)) ?><?= strlen($cat['description'] ?? '') > 50 ? '...' : '' ?></td>
                                    <td class="book-count"><?= $cat['book_count'] ?></td>
                                    <td style="text-align: right;">
                                        <div class="actions">
                                            <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="action-btn">Edit</a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action_type" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="action-btn delete" onclick="return <?= $cat['book_count'] > 0 ? 'alert(\'Cannot delete: ' . $cat['book_count'] . ' books in this category\')' : 'confirm(\'Delete this category?\')' ?>">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">☷</div>
                    <div style="margin-bottom: 20px;">No categories created yet</div>
                    <a href="categories.php?action=add" class="btn">Create first category</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
