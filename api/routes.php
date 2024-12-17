<?php
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:4200',
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/*API Endpoint Router*/

require_once "./modules/get.php";
require_once "./modules/post.php";
require_once __DIR__ . '/connection.php';

// INITIALIZE ESSENTIAL OBJECTS
$pdo = require __DIR__ . '/connection.php';
$get = new TodoGet($pdo);
$post = new ToDoPost($pdo);


// Check if 'request' parameter is set in the request
if (isset($_REQUEST['request'])) {
    // Split the request into an array based on '/'
    $request = explode('/', $_REQUEST['request']);
} else {
    // If 'request' parameter is not set, return a 404 response
    echo "Not Found";
    http_response_code(404);
}

// THIS IS THE MAIN SWITCH STATEMENT
switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        http_response_code(200);
        exit();

    case 'GET':
        switch ($request[0]) {

            case 'testgets':
                echo "get request works";
                break;

            case 'users':
                if (count($request) > 1) {
                    echo json_encode($get->getTodoById($request[1], $request[2]));
                } else {
                    echo json_encode($get->getAllTodos($request[1]));
                }

            default:
                // RESPONSE FOR UNSUPPORTED REQUESTS
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;


    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        switch ($request[0]) {

            case 'createTodo':
                echo json_encode($post->createTodo($data->title, $data->description, $data->due_date, $data->user_id));
                break;

            default:
                // RESPONSE FOR UNSUPPORTED REQUESTS
                echo "No Such Request";
                http_response_code(403);
                break;
        }
        break;

    default:
        // Return a 404 response for unsupported HTTP methods
        echo "Unsupported HTTP method";
        http_response_code(404);
        break;
}

