<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../db.php';

try {
    $conn = $GLOBALS['pdo'] ?? null;
    if (!$conn) {
        throw new Exception('Database connection not available');
    }

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGetCheckWatchlist($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function handleGetCheckWatchlist($conn) {
    // Récupérer les paramètres
    $user_id = $_GET['user_id'] ?? null;
    $items = $_GET['items'] ?? null; // Format: "type:id,type:id" (ex: "movie:1,series:2")
    
    if (!$user_id || !$items) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'user_id and items parameters are required']);
        return;
    }

    // Parser les items
    $itemPairs = explode(',', $items);
    
    // Créer un tableau de résultats formaté avec false par défaut
    $watchlistStatus = [];
    foreach ($itemPairs as $pair) {
        $watchlistStatus[$pair] = false;
    }
    
    // Vérifier chaque item dans la base de données
    foreach ($itemPairs as $pair) {
        list($type, $id) = explode(':', $pair);
        $sql = "SELECT COUNT(*) as count FROM watchlist WHERE user_id = ? AND item_type = ? AND item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $type, PDO::PARAM_STR);
        $stmt->bindValue(3, $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $watchlistStatus[$pair] = true;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($watchlistStatus);
}
?>
