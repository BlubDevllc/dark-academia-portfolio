<?php
/**
 * Biblioteca Obscura - REST API
 * Endpoints for fetching book data from database
 */

header('Content-Type: application/json');
require_once 'db/config.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'get_books';

try {
    if ($action === 'get_books') {
        // Fetch all active books from database
        $query = "SELECT 
                    id, 
                    title, 
                    author, 
                    description, 
                    rating, 
                    image_path,
                    rarity,
                    pages,
                    published_year
                  FROM books 
                  WHERE is_active = TRUE 
                  ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $books,
            'count' => count($books)
        ]);
    } 
    elseif ($action === 'get_categories') {
        // Fetch all categories
        $query = "SELECT id, name, description FROM categories WHERE is_active = TRUE ORDER BY name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    }
    elseif ($action === 'search') {
        // Search books by title or author
        $query = isset($_GET['q']) ? $_GET['q'] : '';
        
        if (strlen($query) < 2) {
            echo json_encode([
                'success' => false,
                'error' => 'Search query too short (minimum 2 characters)'
            ]);
            exit;
        }
        
        $searchTerm = "%$query%";
        $sql = "SELECT 
                    id, 
                    title, 
                    author, 
                    description, 
                    rating, 
                    image_path
                FROM books 
                WHERE is_active = TRUE 
                AND (title LIKE ? OR author LIKE ? OR description LIKE ?)
                ORDER BY title ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $results,
            'query' => $query
        ]);
    }
    else {
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
