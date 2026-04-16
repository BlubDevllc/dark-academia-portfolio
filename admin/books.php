<?php
/**
 * BOOK MANAGEMENT - CRUD OPERATIONS
 * Biblioteca Obscura - Book Archive Management
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
            $title = $_POST['title'] ?? '';
            $author = $_POST['author'] ?? '';
            $description = $_POST['description'] ?? '';
            $category_id = $_POST['category_id'] ?? '';
            $rarity = $_POST['rarity'] ?? 'Common';
            $rating = (int)($_POST['rating'] ?? 0);
            $pages = (int)($_POST['pages'] ?? 0);
            $isbn = !empty($_POST['isbn']) ? $_POST['isbn'] : null;  // NULL for empty ISBN
            $published_year = (int)($_POST['published_year'] ?? date('Y'));
            $language = $_POST['language'] ?? 'English';

            $image_path = '';
            if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $file = $_FILES['book_image'];
                $filename = uniqid() . '_' . basename($file['name']);
                $target_file = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $image_path = 'assets/uploads/' . $filename;
                }
            }

            $stmt = $db->prepare("
                INSERT INTO books (title, author, description, image_path, category_id, rarity, rating, pages, isbn, published_year, language, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([$title, $author, $description, $image_path, $category_id, $rarity, $rating, $pages, $isbn, $published_year, $language]);
            $book_id = $db->lastInsertId();

            logAdminAction($db, 'add_book', $book_id, "Added: $title by $author");
            $message = "✓ Book added successfully!";
            $action = 'list';
        } catch (Exception $e) {
            $error = "Error adding book: " . $e->getMessage();
        }
    }

    if ($action_type === 'edit') {
        try {
            $id = $_POST['book_id'] ?? '';
            $title = $_POST['title'] ?? '';
            $author = $_POST['author'] ?? '';
            $description = $_POST['description'] ?? '';
            $category_id = $_POST['category_id'] ?? '';
            $rarity = $_POST['rarity'] ?? 'Common';
            $rating = (int)($_POST['rating'] ?? 0);
            $pages = (int)($_POST['pages'] ?? 0);
            $isbn = !empty($_POST['isbn']) ? $_POST['isbn'] : null;  // NULL for empty ISBN
            $published_year = (int)($_POST['published_year'] ?? date('Y'));
            $language = $_POST['language'] ?? 'English';

            // Get current image path
            $current = $db->query("SELECT image_path FROM books WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            $image_path = $current['image_path'] ?? '';

            // Handle new image upload
            if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/uploads/';
                $file = $_FILES['book_image'];
                $filename = uniqid() . '_' . basename($file['name']);
                $target_file = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    // Delete old image if exists
                    if ($image_path && file_exists('../' . $image_path)) {
                        unlink('../' . $image_path);
                    }
                    $image_path = 'assets/uploads/' . $filename;
                }
            }

            $stmt = $db->prepare("
                UPDATE books 
                SET title = ?, author = ?, description = ?, image_path = ?, category_id = ?, 
                    rarity = ?, rating = ?, pages = ?, isbn = ?, published_year = ?, language = ?, updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([$title, $author, $description, $image_path, $category_id, $rarity, $rating, $pages, $isbn, $published_year, $language, $id]);

            logAdminAction($db, 'edit_book', $id, "Updated: $title");
            $message = "✓ Book updated successfully!";
            $action = 'list';
        } catch (Exception $e) {
            $error = "Error updating book: " . $e->getMessage();
        }
    }

    if ($action_type === 'delete') {
        try {
            $id = $_POST['book_id'] ?? '';
            
            // Get book details
            $book = $db->query("SELECT title, image_path FROM books WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            
            // Delete image if exists
            if ($book['image_path'] && file_exists('../' . $book['image_path'])) {
                unlink('../' . $book['image_path']);
            }

            // Delete book
            $db->exec("DELETE FROM books WHERE id = $id");
            
            logAdminAction($db, 'delete_book', $id, "Deleted: {$book['title']}");
            $message = "✓ Book deleted successfully!";
            $action = 'list';
        } catch (Exception $e) {
            $error = "Error deleting book: " . $e->getMessage();
        }
    }
}

// Get all books and categories
$books = getAllBooks($db);
$categories = getAllCategories($db);

// Get single book for editing
$book = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $book = getBookById($db, $_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Biblioteca Obscura</title>
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
            max-width: 1200px;
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
            margin-bottom: 40px;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        label {
            color: #C2A35D;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="file"],
        textarea,
        select {
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
        input[type="number"]:focus,
        input[type="file"]:focus,
        textarea:focus,
        select:focus {
            background: rgba(28, 27, 26, 0.95);
            box-shadow: 0 0 20px rgba(194, 163, 93, 0.3);
            border-color: #E2D3B7;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
        }

        .current-image {
            margin-top: 10px;
            max-width: 150px;
            border-radius: 4px;
            opacity: 0.7;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* Books list */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .book-card {
            background: rgba(60, 47, 47, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 6px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(194, 163, 93, 0.2);
        }

        .book-image {
            width: 100%;
            height: 200px;
            background: rgba(28, 27, 26, 0.8);
            object-fit: cover;
        }

        .book-content {
            padding: 20px;
        }

        .book-title {
            font-size: 1.3rem;
            color: #C2A35D;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .book-author {
            color: #8B7D75;
            font-size: 0.95rem;
            margin-bottom: 10px;
            font-style: italic;
        }

        .book-meta {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 0.85rem;
            color: #8B7D75;
        }

        .rarity-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .rarity-common {
            background: rgba(150, 150, 150, 0.3);
            color: #c0c0c0;
        }

        .rarity-uncommon {
            background: rgba(100, 200, 100, 0.2);
            color: #90ff90;
        }

        .rarity-rare {
            background: rgba(100, 100, 200, 0.2);
            color: #90c0ff;
        }

        .rarity-legendary {
            background: rgba(194, 163, 93, 0.2);
            color: #C2A35D;
        }

        .book-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .book-actions button {
            flex: 1;
            padding: 10px;
            border: 1px solid #C2A35D;
            background: rgba(194, 163, 93, 0.1);
            color: #C2A35D;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .book-actions button:hover {
            background: rgba(194, 163, 93, 0.2);
        }

        .book-actions .delete-btn {
            background: rgba(255, 70, 70, 0.1);
            border-color: #ff4646;
            color: #ff9999;
        }

        .book-actions .delete-btn:hover {
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

        .empty-text {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .form-container {
                padding: 20px;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Manage Books</h1>
            <div class="header-buttons">
                <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
                <?php if ($action === 'list'): ?>
                    <a href="books.php?action=add" class="btn">+ Add New Book</a>
                <?php else: ?>
                    <a href="books.php" class="btn btn-secondary">View All Books</a>
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
                <h2 class="form-title"><?= $action === 'add' ? '✦ Add New Book' : '◊ Edit Book' ?></h2>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
                    <?php if ($action === 'edit' && $book): ?>
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" value="<?= $book['title'] ?? '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="author">Author *</label>
                            <input type="text" id="author" name="author" value="<?= $book['author'] ?? '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Select a category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($book && $book['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rarity">Rarity</label>
                            <select id="rarity" name="rarity">
                                <option value="Common" <?= ($book && ($book['rarity'] ?? '') === 'Common') ? 'selected' : '' ?>>Common</option>
                                <option value="Uncommon" <?= ($book && ($book['rarity'] ?? '') === 'Uncommon') ? 'selected' : '' ?>>Uncommon</option>
                                <option value="Rare" <?= ($book && ($book['rarity'] ?? '') === 'Rare') ? 'selected' : '' ?>>Rare</option>
                                <option value="Legendary" <?= ($book && ($book['rarity'] ?? '') === 'Legendary') ? 'selected' : '' ?>>Legendary</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rating">Rating (1-5)</label>
                            <input type="number" id="rating" name="rating" value="<?= $book['rating'] ?? '5' ?>" min="1" max="5">
                        </div>

                        <div class="form-group">
                            <label for="pages">Pages</label>
                            <input type="number" id="pages" name="pages" value="<?= $book['pages'] ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="<?= $book['isbn'] ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="published_year">Published Year</label>
                            <input type="number" id="published_year" name="published_year" value="<?= $book['published_year'] ?? date('Y') ?>">
                        </div>

                        <div class="form-group">
                            <label for="language">Language</label>
                            <input type="text" id="language" name="language" value="<?= $book['language'] ?? 'English' ?>">
                        </div>

                        <div class="form-group form-group-full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?= $book['description'] ?? '' ?></textarea>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="book_image">Book Cover Image</label>
                            <?php if ($book && $book['image_path']): ?>
                                <img src="../<?= htmlspecialchars($book['image_path']) ?>" alt="Current cover" class="current-image">
                            <?php endif; ?>
                            <input type="file" id="book_image" name="book_image" accept="image/*">
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn">✓ <?= $action === 'add' ? 'Add Book' : 'Update Book' ?></button>
                        <a href="books.php" class="btn btn-secondary">✗ Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Books List -->
        <?php if ($action === 'list'): ?>
            <?php if (count($books) > 0): ?>
                <div class="books-grid">
                    <?php foreach ($books as $b): ?>
                        <div class="book-card">
                            <?php if ($b['image_path']): ?>
                                <img src="../<?= htmlspecialchars($b['image_path']) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="book-image">
                            <?php else: ?>
                                <div class="book-image" style="display: flex; align-items: center; justify-content: center;">◊</div>
                            <?php endif; ?>
                            <div class="book-content">
                                <div class="book-title"><?= htmlspecialchars($b['title']) ?></div>
                                <div class="book-author">by <?= htmlspecialchars($b['author']) ?></div>
                                
                                <div class="book-meta">
                                    <span class="rarity-badge rarity-<?= strtolower($b['rarity']) ?>"><?= $b['rarity'] ?></span>
                                    <span>★ <?= $b['rating'] ?>/5</span>
                                </div>

                                <div class="book-meta">
                                    <span><?= $b['pages'] ?> pages</span>
                                    <span><?= $b['published_year'] ?></span>
                                </div>

                                <div class="book-meta">
                                    Category: <?= htmlspecialchars($b['category_name'] ?? 'Uncategorized') ?>
                                </div>

                                <div class="book-actions">
                                    <a href="books.php?action=edit&id=<?= $b['id'] ?>" style="text-decoration: none;">
                                        <button type="button">✎ Edit</button>
                                    </a>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="action_type" value="delete">
                                        <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Delete this book?')">✗ Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">◊</div>
                    <div class="empty-text">No books in the archive yet</div>
                    <a href="books.php?action=add" class="btn">Add the first book</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
