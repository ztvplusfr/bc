<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Simple router
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/backend', '', $path);

switch ($path) {
    case '/api/movies':
    case '/api/movies/':
        require_once 'api/movies.php';
        break;
    case '/api/series':
    case '/api/series/':
        require_once 'api/series.php';
        break;
    case '/api/watchlist':
    case '/api/watchlist/':
        require_once 'api/watchlist.php';
        break;
    case '/api/watchlist-check':
    case '/api/watchlist-check/':
        require_once 'api/watchlist-check.php';
        break;
    case '/api/hero':
    case '/api/hero/':
        require_once 'api/hero.php';
        break;
    case '/api/auth':
    case '/api/auth/':
        require_once 'api/auth.php';
        break;
    case '/':
    case '':
        echo json_encode([
            'message' => 'Backend API Server',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /api/movies' => 'List all movies',
                'GET /api/movies?id=1' => 'Get specific movie',
                'POST /api/movies' => 'Create new movie',
                'PUT /api/movies?id=1' => 'Update movie',
                'DELETE /api/movies?id=1' => 'Delete movie',
                'GET /api/hero' => 'List hero content'
            ]
        ]);
        break;
    default:
        // Check if path starts with /api/movies
        if (strpos($path, '/api/movies') === 0) {
            require_once 'api/movies.php';
            break;
        }
        // Check if path starts with /api/series
        if (strpos($path, '/api/series') === 0) {
            require_once 'api/series.php';
            break;
        }
        // Check if path starts with /api/watchlist but not watchlist-check
        if (strpos($path, '/api/watchlist') === 0 && strpos($path, '/api/watchlist-check') !== 0) {
            require_once 'api/watchlist.php';
            break;
        }
        // Check if path starts with /api/auth
        if (strpos($path, '/api/auth') === 0) {
            require_once 'api/auth.php';
            break;
        }
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>
