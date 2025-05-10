
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


if (empty($_POST['token'])) {
  echo json_encode([
    'status' => 'error',
    'message' => 'token not present',
  ]);
  exit; 
}
$token = $_POST['token'];

$auth = new AuthSystem();
$auth->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
$result = $auth->token_validation($token);
echo json_encode($result);






