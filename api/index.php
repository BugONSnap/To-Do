<?php
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors to browser
ini_set('log_errors', '1'); // Log errors instead
ini_set('error_log', __DIR__ . '/error.log'); // Set error log path

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Add this at the start of the file
error_log("Request received: " . $_SERVER['REQUEST_URI']);
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);

// Get the request URI and remove the base path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/To-Do/api/', '', $uri); // Adjust this based on your actual path
$uri = explode('/', $uri);

// Remove empty elements
$uri = array_values(array_filter($uri));

if (empty($uri)) {
    http_response_code(404);
    echo json_encode(['error' => 'No endpoint specified']);
    exit;
}

// After URI processing
error_log("Processed URI: " . print_r($uri, true));
error_log("Endpoint: " . ($endpoint ?? 'none'));

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/../modules/auth.php';
require_once __DIR__ . '/../modules/get.php';
require_once __DIR__ . '/../modules/post.php';

$endpoint = $uri[0];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($endpoint) {
        case 'auth':
            error_log("Auth endpoint hit");
            $auth = new Auth($pdo);
            
            if ($uri[1] === 'register' && $method === 'POST') {
                error_log("Register route hit");
                $data = json_decode(file_get_contents('php://input'), true);
                error_log("Register data received: " . print_r($data, true));
                $result = $auth->register(
                    $data['username'] ?? '',
                    $data['email'] ?? '',
                    $data['password'] ?? ''
                );
                error_log("Register result: " . print_r($result, true));
                echo json_encode($result);
            }
            elseif ($uri[1] === 'login' && $method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $auth->login(
                    $data['username'] ?? '',
                    $data['password'] ?? ''
                );
                echo json_encode($result);
            }
            break;

        case 'todos':
            if ($method === 'GET') {
                $todoGet = new TodoGet($pdo);
                $user_id = $_GET['user_id'] ?? null;
                
                if (isset($uri[1])) {
                    $result = $todoGet->getTodoById($uri[1], $user_id);
                } else {
                    $result = $todoGet->getAllTodos($user_id);
                }
                echo json_encode($result);
            }
            elseif ($method === 'POST') {
                $todoPost = new TodoPost($pdo);
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $todoPost->createTodo(
                    $data['title'],
                    $data['description'],
                    $data['due_date'],
                    $data['user_id']
                );
                echo json_encode($result);
            }
            elseif ($method === 'PUT' && isset($uri[1])) {
                $todoPost = new TodoPost($pdo);
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $todoPost->updateTodo(
                    $uri[1],
                    $data['title'],
                    $data['description'],
                    $data['status'],
                    $data['due_date'],
                    $data['user_id']
                );
                echo json_encode($result);
            }
            elseif ($method === 'DELETE' && isset($uri[1])) {
                $todoPost = new TodoPost($pdo);
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $todoPost->deleteTodo($uri[1], $data['user_id']);
                echo json_encode($result);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 