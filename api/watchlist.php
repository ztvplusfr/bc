<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetWatchlist($pdo);
        break;
    case 'POST':
        handlePostWatchlist($pdo);
        break;
    case 'DELETE':
        handleDeleteWatchlist($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGetWatchlist(PDO $pdo): void {
    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    $itemType = isset($_GET['item_type']) ? $_GET['item_type'] : null;

    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id is required']);
        return;
    }

    $sql = 'SELECT id, user_id, item_id, item_type, created_at FROM watchlist WHERE user_id = :user_id';
    $params = ['user_id' => $userId];

    if ($itemType && in_array($itemType, ['movie', 'series'], true)) {
        $sql .= ' AND item_type = :item_type';
        $params['item_type'] = $itemType;
    }

    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode($rows);
}

function handlePostWatchlist(PDO $pdo): void {
    $input = json_decode(file_get_contents('php://input'), true);

    $userId = isset($input['user_id']) ? (int) $input['user_id'] : 0;
    $itemId = isset($input['item_id']) ? (int) $input['item_id'] : 0;
    $itemType = isset($input['item_type']) ? $input['item_type'] : null;

    if ($userId <= 0 || $itemId <= 0 || !in_array($itemType, ['movie', 'series'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id, item_id and valid item_type are required']);
        return;
    }

    $sql = 'INSERT INTO watchlist (user_id, item_id, item_type) VALUES (:user_id, :item_id, :item_type)
            ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'item_id' => $itemId,
        'item_type' => $itemType,
    ]);

    echo json_encode([
        'user_id' => $userId,
        'item_id' => $itemId,
        'item_type' => $itemType,
    ]);
}

function handleDeleteWatchlist(PDO $pdo): void {
    $input = json_decode(file_get_contents('php://input'), true);

    $userId = isset($input['user_id']) ? (int) $input['user_id'] : 0;
    $itemId = isset($input['item_id']) ? (int) $input['item_id'] : 0;
    $itemType = isset($input['item_type']) ? $input['item_type'] : null;

    if ($userId <= 0 || $itemId <= 0 || !in_array($itemType, ['movie', 'series'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id, item_id and valid item_type are required']);
        return;
    }

    $sql = 'DELETE FROM watchlist WHERE user_id = :user_id AND item_id = :item_id AND item_type = :item_type';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'item_id' => $itemId,
        'item_type' => $itemType,
    ]);

    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'item_id' => $itemId,
        'item_type' => $itemType,
    ]);
}

