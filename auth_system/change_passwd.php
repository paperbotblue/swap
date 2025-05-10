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

if (!isset($_POST['token'], $_POST['old_password'], $_POST['new_password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Required fields are missing."
    ]);
    exit;
}

$token = htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8');
$old_password = htmlspecialchars($_POST['old_password'], ENT_QUOTES, 'UTF-8');
$new_password = htmlspecialchars($_POST['new_password'], ENT_QUOTES, 'UTF-8');

if (empty($token) || empty($old_password) || empty($new_password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input. Fields cannot be empty."
    ]);
    exit;
}

include_once __DIR__ . "/auth.php";
include_once __DIR__ . "/../config.php";

$auth = new AuthSystem();
$auth->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
$result = $auth->changePassword($token, $old_password, $new_password);
echo json_encode($result);



