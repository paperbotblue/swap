
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

$email = htmlspecialchars($_POST['email']);
$password = htmlspecialchars($_POST['password']);

$auth = new AuthSystem();
$auth->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
$auth->register($email, $password);

echo json_encode([
  "status" => "success",
  "message" => "User registered successfully."
]);

?>
