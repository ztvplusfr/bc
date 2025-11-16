<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$dataDir = __DIR__ . '/../data/movies/';

switch ($method) {
    case 'GET':
        handleGet($dataDir);
        break;
    case 'POST':
        handlePost($dataDir);
        break;
    case 'PUT':
        handlePut($dataDir);
        break;
    case 'DELETE':
        handleDelete($dataDir);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGet($dataDir) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : (isset($_GET['q']) ? trim($_GET['q']) : '');
    
    if ($id) {
        // Get specific movie
        $file = $dataDir . $id . '.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            echo $json;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Movie not found']);
        }
    } else {
        // Get all movies (optionally filtered by search)
        $movies = [];
        $files = glob($dataDir . '*.json');
        
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $movie = json_decode($json, true);

            if (!$movie) {
                continue;
            }

            if ($search !== '') {
                $haystack = (
                    ($movie['title'] ?? '') . ' ' .
                    ($movie['original_title'] ?? '') . ' ' .
                    ($movie['description'] ?? '')
                );

                if (stripos($haystack, $search) === false) {
                    continue;
                }
            }

            $movies[] = $movie;
        }
        
        echo json_encode($movies);
    }
}

function handlePost($dataDir) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['title'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    // Generate new ID
    $files = glob($dataDir . '*.json');
    $maxId = 0;
    foreach ($files as $file) {
        $id = basename($file, '.json');
        if (is_numeric($id) && $id > $maxId) {
            $maxId = $id;
        }
    }
    $newId = $maxId + 1;
    
    $input['id'] = $newId;
    $input['created_at'] = date('c');
    $input['updated_at'] = date('c');
    
    $file = $dataDir . $newId . '.json';
    file_put_contents($file, json_encode($input, JSON_PRETTY_PRINT));
    
    echo json_encode($input);
}

function handlePut($dataDir) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        return;
    }
    
    $file = $dataDir . $id . '.json';
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }
    
    $input['id'] = $id;
    $input['updated_at'] = date('c');
    
    file_put_contents($file, json_encode($input, JSON_PRETTY_PRINT));
    echo json_encode($input);
}

function handleDelete($dataDir) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        return;
    }
    
    $file = $dataDir . $id . '.json';
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found']);
        return;
    }
    
    unlink($file);
    echo json_encode(['success' => 'Movie deleted']);
}
?>
