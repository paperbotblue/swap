<?php
require_once "../database_system/database.php";
require_once "../config.php";

/* header("Access-Control-Allow-Origin: *"); */
/* header("Access-Control-Allow-Headers: Content-Type, Authorization"); */
/* header("Access-Control-Allow-Methods: POST"); */
/* header("Content-Type: multipart/form-data"); */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
  exit;
}

$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);

// Validate and sanitize input

if ( !isset($_POST['amount'], $_POST['date'], $_POST['status'], $_POST['subItems'], $_POST['phone_number'])) {
  http_response_code(400);
  echo json_encode(['status' => 'fail', 'message' => 'Missing or invalid input']);
  exit;
}

$amount = filter_var($_POST['amount'], FILTER_VALIDATE_INT);
$date = htmlspecialchars(trim($_POST['date']));
$status = htmlspecialchars(trim($_POST['status']));
$phone_number = htmlspecialchars(trim($_POST['phone_number']));
$subItems = json_decode($_POST['subItems'], true);

if ($status == "Completed") {
  $status = 1;
} else {
  $status = 0;
}

if ($amount === false) {
  http_response_code(400);
  echo json_encode(['status' => 'fail', 'message' => 'Invalid amount or subItems']);
  exit;
}

try {
  $purchase_id = $db->create('user_purchases', [
    'phone_number' => $phone_number,
    'date' => $date,
    'status' => $status,
    'amount' => $amount
  ]);

  foreach ($subItems as $item) {
    if (!isset($item['name'], $item['quantity'], $item['total'])) continue;

    $item_name = htmlspecialchars(trim($item['name']));
    $item_quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
    $item_total = filter_var($item['total'], FILTER_VALIDATE_FLOAT);

    if ($item_quantity === false || $item_total === false) continue;

    $db->create('user_purchase_details', [
      'purchase_id' => $purchase_id,
      'item_category' => 'default',
      'item_name' => $item_name,
      'item_quantity' => $item_quantity,
      'item_total' => $item_total
    ]);
  }

  echo json_encode(['status' => 'success', 'message' => 'Purchase recorded']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['status' => 'fail', 'message' => 'Server error, please try again later']);
}

