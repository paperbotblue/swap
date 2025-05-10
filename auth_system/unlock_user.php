<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../auth_system/auth.php";
require_once __DIR__ . "/../config.php";

if (!isset($_GET['token']) || empty($_GET['token'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Verification token is missing.'
    ]);
    exit;
}

$token = $_GET['token'];
$auth = new AuthSystem;
$auth->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
$user = $auth->read("users", "unlock_token", $token);

if (!$user || $user[0]['unlock_token_ttl'] < time()) {
  $auth->update("users", "id", $user[0]['id'], "unlock_token", '');
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid or expired verification token.'
  ]);
  exit;
}

$password = bin2hex(random_bytes(30));
$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$auth->update("users", "id", $user[0]['id'], "password", $passwordHash);

$auth->sendPasswordToMail($user[0]['email'], $password);

echo json_encode([
  "status" => "success",
  "message" => "password reseted successfully"
]);


