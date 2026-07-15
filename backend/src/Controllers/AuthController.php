<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/jwt.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register($data) {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Required: username, email, password."]);
            return;
        }

        $userId = $this->user->create($data['username'], $data['email'], $data['password']);

        if ($userId) {
            http_response_code(201);
            echo json_encode(["message" => "User was registered successfully.", "user_id" => $userId]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Unable to register user. Username or email may already be in use."]);
        }
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Required: email, password."]);
            return;
        }

        $userData = $this->user->findByEmail($data['email']);

        if ($userData && password_verify($data['password'], $userData['password_hash'])) {
            // Generate token payload
            $token_payload = [
                "iss" => "dsa_oj",
                "iat" => time(),
                "exp" => time() + (3600 * 24), // 24 hours
                "user" => [
                    "id" => $userData['id'],
                    "username" => $userData['username'],
                    "email" => $userData['email'],
                    "role" => $userData['role']
                ]
            ];

            $jwt = JWT::encode($token_payload);

            http_response_code(200);
            echo json_encode([
                "message" => "Login successful.",
                "token" => $jwt,
                "user" => [
                    "id" => $userData['id'],
                    "username" => $userData['username'],
                    "role" => $userData['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Login failed. Invalid email or password."]);
        }
    }

    public function getProfile($userId) {
        $profile = $this->user->findById($userId);
        if ($profile) {
            http_response_code(200);
            echo json_encode($profile);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
        }
    }
}
