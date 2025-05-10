<?php
require_once "../database_system/database.php";
require_once "../config.php";

/* header("Access-Control-Allow-Origin: *"); */
/* header("Access-Control-Allow-Headers: Content-Type"); */
/* header("Content-Type: multipart/form-data"); */
/* header("Access-Control-Allow-Methods: POST"); */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
  exit;
}

$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD); // update credentials


$phone_number = $_POST['phone_number'];
  $purchases = $db->read("user_purchases", "phone_number", $phone_number);
  if (!$purchases) return [];

  foreach ($purchases as &$purchase) {
    $details = $db->read("user_purchase_details", "purchase_id", $purchase['id']);
    $purchase["marginEarned"] = $purchase['amount'];
    $purchase["status"] = ($purchase["status"] == 0) ? "Not Paid" : "Paid";
    $purchase["user_purchase_details"] = $details ?: [];
  }

header('Content-Type: application/json');
echo json_encode($purchases);
