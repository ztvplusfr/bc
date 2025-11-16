<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        handleSyncUser($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * Synchronise un utilisateur Auth0 dans la table `users`.
 *
 * Hypothèses sur la table `users` :
 * - id INT AUTO_INCREMENT PRIMARY KEY
 * - auth0_id VARCHAR(255) UNIQUE
 * - email VARCHAR(255) NULL
 * - name VARCHAR(255) NULL
 * - created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * - updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 */
function handleSyncUser(PDO $pdo): void {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        return;
    }

    $auth0Id = isset($input['auth0_id']) ? trim((string) $input['auth0_id']) : '';
    $email = isset($input['email']) ? trim((string) $input['email']) : null;
    $name = isset($input['name']) ? trim((string) $input['name']) : null;

    if ($auth0Id === '') {
        http_response_code(400);
        echo json_encode(['error' => 'auth0_id is required']);
        return;
    }

    try {
        // On suppose qu'il existe une colonne auth0_sub NOT NULL dans la table
        // et on la remplit avec la même valeur que auth0_id (le sub Auth0)
        $sql = 'INSERT INTO users (auth0_id, auth0_sub, email, name)
                VALUES (:auth0_id, :auth0_sub, :email, :name)
                ON DUPLICATE KEY UPDATE
                    email = VALUES(email),
                    name = VALUES(name),
                    updated_at = CURRENT_TIMESTAMP';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'auth0_id' => $auth0Id,
            'auth0_sub' => $auth0Id,
            'email' => $email,
            'name' => $name,
        ]);

        // Récupérer la ligne finale
        $select = $pdo->prepare(
            'SELECT id, auth0_id, auth0_sub, email, name, created_at, updated_at 
             FROM users 
             WHERE auth0_id = :auth0_id OR auth0_sub = :auth0_sub
             LIMIT 1'
        );
        $select->execute([
            'auth0_id' => $auth0Id,
            'auth0_sub' => $auth0Id,
        ]);
        $user = $select->fetch();

        echo json_encode([
            'success' => true,
            'user' => $user,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to sync user',
            'details' => $e->getMessage(),
        ]);
    }
}
