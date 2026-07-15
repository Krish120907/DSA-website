<?php
// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/ProblemController.php';
require_once __DIR__ . '/../src/Controllers/SubmissionController.php';
require_once __DIR__ . '/../src/Controllers/LeaderboardController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

// Expected URL structure: /api/resource or /api/resource/id or /api/resource/id/subresource
if ($uriSegments[0] !== 'api') {
    http_response_code(404);
    echo json_encode(["message" => "Not Found"]);
    exit();
}

$resource = isset($uriSegments[1]) ? $uriSegments[1] : null;
$resourceId = isset($uriSegments[2]) ? $uriSegments[2] : null;
$subResource = isset($uriSegments[3]) ? $uriSegments[3] : null;

$method = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$params = $_GET;

switch ($resource) {
    case 'auth':
        $authController = new AuthController();
        if ($method === 'POST' && $resourceId === 'register') {
            $authController->register($body);
        } elseif ($method === 'POST' && $resourceId === 'login') {
            $authController->login($body);
        } elseif ($method === 'GET' && $resourceId === 'profile') {
            $user = AuthMiddleware::authenticate();
            $authController->getProfile($user['id']);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Auth Endpoint Not Found"]);
        }
        break;

    case 'problems':
        $problemController = new ProblemController();
        if ($method === 'GET') {
            if ($resourceId === null) {
                // List problems (can filter via query params)
                $problemController->list($params);
            } else {
                // Detail of a problem
                if ($subResource === 'samples') {
                    // Public sample test cases
                    $problemController->getSamples($resourceId);
                } elseif ($subResource === 'testcases') {
                    // Full test cases (sample + hidden) for running locally. Requires auth.
                    AuthMiddleware::authenticate();
                    $problemController->getAllTestCases($resourceId);
                } else {
                    $problemController->detail($resourceId);
                }
            }
        } elseif ($method === 'POST') {
            $user = AuthMiddleware::authenticate();
            $problemController->submitProblem($user['id'], $body);
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
        break;

    case 'submissions':
        $submissionController = new SubmissionController();
        if ($method === 'POST') {
            $user = AuthMiddleware::authenticate();
            $submissionController->create($user['id'], $body);
        } elseif ($method === 'GET') {
            $user = AuthMiddleware::authenticate();
            if ($resourceId === 'history') {
                $submissionController->getHistory($user['id']);
            } elseif ($resourceId === 'problem' && $subResource !== null) {
                $submissionController->getProblemHistory($subResource, $user['id']);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid submission endpoint query."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
        break;

    case 'leaderboard':
        $leaderboardController = new LeaderboardController();
        if ($method === 'GET') {
            $leaderboardController->get();
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
        break;

    case 'admin':
        $adminController = new AdminController();
        $user = AuthMiddleware::requireAdmin();

        if ($method === 'POST' && $resourceId === 'problems') {
            if ($subResource !== null && isset($uriSegments[4]) && $uriSegments[4] === 'approve') {
                $adminController->approveProblem($subResource);
            } else {
                $adminController->createProblem($user['id'], $body);
            }
        } elseif ($method === 'GET' && $resourceId === 'problems') {
            $adminController->listProblems($params);
        } elseif ($method === 'PUT' && $resourceId === 'problems' && $subResource !== null) {
            $adminController->updateProblem($subResource, $body);
        } elseif ($method === 'DELETE' && $resourceId === 'problems' && $subResource !== null) {
            $adminController->deleteProblem($subResource);
        } elseif ($method === 'POST' && $resourceId === 'testcases') {
            $adminController->addTestCase($body);
        } elseif ($method === 'DELETE' && $resourceId === 'testcases' && $subResource !== null) {
            $adminController->deleteTestCase($subResource);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Admin Action Not Found"]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["message" => "Resource Not Found"]);
        break;
}
?>
