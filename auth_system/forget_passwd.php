<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); 
  echo json_encode([
    "status" => "error",
    "message" => "Invalid request method."
  ]);
}

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../config.php";

$email = $_POST['email'] ?? '';

// Validate fields
if (empty($email)) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Required fields are missing',
  ]);
  exit;
}

$auth = new AuthSystem();
$auth->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
$user = $auth->read("users", "email", $email);

if (!$user) {
  echo json_encode([
    'status' => 'error',
    'message' => 'email user not found'
  ]);
  exit;
}

$auth->sendVerificationEmailPasswdChange($email);
echo json_encode([
  'status' => 'success',
  'message' => 'email link sent'
]);


