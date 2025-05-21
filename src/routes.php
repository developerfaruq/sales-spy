<?php
// Include autoloader and dependencies
$autoloadPath = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!file_exists($autoloadPath)) {
    die(json_encode(['error' => 'Autoloader not found. Please run "composer install" in the project root.']));
}
require_once $autoloadPath;

require_once __DIR__ . '/controllers/AuthController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();

try {
    if ($uri === '/api/signup' && $method === 'POST') {
        $data = $_POST;
        header('Content-Type: application/json');
        echo json_encode($authController->signup($data));
    } elseif ($uri === '/api/login' && $method === 'POST') {
        $data = $_POST;
        header('Content-Type: application/json');
        echo json_encode($authController->login($data));
    } elseif ($uri === '/api/reset-password' && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true) ?: [];
        header('Content-Type: application/json');
        echo json_encode($authController->requestPasswordReset($data));
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid endpoint or method']);
        http_response_code(404);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    http_response_code(500);
}
?>