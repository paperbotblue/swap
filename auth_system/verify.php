<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../database_system/database.php";
require_once __DIR__ . "/../config.php";

if (!isset($_GET['token']) || empty($_GET['token'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Verification token is missing.'
    ]);
    exit;
}

$token = $_GET['token'];

try {
  $db = new Database;
  $db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
  $user = $db->read("users", "verification_token", $token);
  if($user[0]['verification_token_ttl'] < time()) {
    $db->delete("users", "id", $user[0]["id"]);
    echo json_encode([
      'status' => 'error',
      'message' => 'verification token expired'
    ]);
    exit;
  }
  if (!$user) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid or expired verification token.'
    ]);
    exit;
  }

  $db->update("users", "id", $user[0]['id'], "verification_token", '');
  $db->update("users", "id", $user[0]['id'], "verification_token_ttl", 0);
  $db->update("users", "id", $user[0]['id'], "is_verified", 1);

  $data = [
    "id" => $user[0]['id'],
    "email" => $user[0]['email']
  ];
  $da = [
    "email" => $user[0]['email']
  ];
  $db->create("user_profiles", $data);
  $db->create("user_courses", $da);
  echo json_encode([
    'status' => 'success',
    'message' => 'Your email has been successfully verified!'
  ]);
} catch (Exception $e) {
    // Handle exceptions
    error_log($e->getMessage(), 3, 'error_log.txt'); // Log the error to a file
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing your request. Please try again later.'
    ]);
}
