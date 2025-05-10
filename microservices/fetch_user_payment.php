<?php
/* header("Access-Control-Allow-Origin: *"); */
/* header("Content-Type: application/json"); */
/* header("Access-Control-Allow-Methods: POST"); */
/* header("Access-Control-Allow-Headers: Content-Type"); */
/**/
require_once '../database_system/database.php';
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
    exit;
}

$phone_number = $_POST['phone_number'] ?? '';
if (empty($phone_number)) {
    echo json_encode([
        "status" => "error",
        "message" => "Phone number is required."
    ]);
    exit;
}

$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);

if (!$db->db_handler) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);
    exit;
}

$user_sales = $db->read("payment_history", "phone_number", $phone_number);

if ($user_sales === false || empty($user_sales)) {
    echo json_encode([
        "status" => "fail",
        "message" => "No records found.",
        "data" => []
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Records fetched successfully.",
    "data" => $user_sales
]);

