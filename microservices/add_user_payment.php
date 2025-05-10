<?php
require_once "../database_system/database.php";
require_once "../config.php";

/* header("Access-Control-Allow-Origin: *"); */
/* header("Access-Control-Allow-Headers: Content-Type"); */
/* header("Access-Control-Allow-Methods: POST"); */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
  exit;
}

$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);

$date = $_POST['date'] ?? '';
$amount = $_POST['amount'] ?? '';
$phone_no = $_POST['phone_no'] ?? '';
$payment_ss = $_FILES['ss'] ?? null;

if (empty($date) || empty($amount) || empty($phone_no) || !$payment_ss) {
  echo json_encode(['status' => 'fail', 'message' => 'Missing required fields']);
  exit;
}

if (
  $payment_ss['error'] !== UPLOAD_ERR_OK ||
  !is_uploaded_file($payment_ss['tmp_name'])
) {
  echo json_encode(['status' => 'fail', 'message' => 'Invalid file upload']);
  exit;
}

$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$ext = pathinfo($payment_ss['name'], PATHINFO_EXTENSION);
$unique_name = uniqid('ss_', true) . '.' . $ext;
$payment_ss_path = $upload_dir . $unique_name;
$public_url = "https://zivaworld.online/uploads/" . $unique_name;

// Move uploaded file
if (!move_uploaded_file($payment_ss['tmp_name'], $payment_ss_path)) {
  echo json_encode(['status' => 'fail', 'message' => 'File upload failed']);
  exit;
}

// Prepare and insert data
$data = [
  "phone_number" => $phone_no,
  "date" => $date,
  "amount" => $amount,
  "screenshot_src" => $public_url
];

$inserted_id = $db->create("payment_history", $data);
if (!$inserted_id) {
  echo json_encode(['status' => 'fail', 'message' => 'Database insert failed']);
  exit;
}

echo json_encode(['status' => 'success', 'message' => 'Payment uploaded successfully', 'id' => $inserted_id]);

