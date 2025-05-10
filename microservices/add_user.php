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

// Instantiate DB
$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD); // update credentials

// Get form fields
$username = $_POST['username'] ?? '';
$business_name = $_POST['businessName'] ?? '';
$phone_number = $_POST['mobileNo'] ?? '';
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password
$address = $_POST['address'] ?? '';
$joining_date = $_POST['joiningDate'] ?? date("Y-m-d H:i:s");

$frontImage = $_FILES['image'] ?? null;
$qrImage = $_FILES['qr'] ?? null;

$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$frontImageName = basename($frontImage['name']);
$qrImageName = basename($qrImage['name']);

$frontImagePath = $upload_dir . $frontImageName;
$qrImagePath = $upload_dir . $qrImageName;

$front_uploaded = move_uploaded_file($frontImage['tmp_name'], $frontImagePath);
$qr_uploaded = move_uploaded_file($qrImage['tmp_name'], $qrImagePath);

if (!$front_uploaded || !$qr_uploaded) {
  echo json_encode(['status' => 'fail', 'message' => 'File upload failed']);
  exit;
}
$result = $db->read('users', 'phone_number', $phone_number);
if ($result && count($result) > 0) {
  echo json_encode([
    'status' => 'error',
    'message' => 'User already exists.',
  ]);
  exit;
}


// Insert into DB
$data = [
  'username' => $username,
  'business_name' => $business_name,
  'phone_number' => $phone_number,
  'password' => $password,
  'front_image' => "https://zivaworld.online/uploads/" . basename($frontImagePath), // store just filename
  'qr_image' => "https://zivaworld.online/uploads/" . basename($qrImageName),
  'address' => $address,
  'joining_date' => $joining_date,
  'is_admin' => 0,
  'total_margin_remaining' => 0,
  'total_items_sold' => 0,
  'total_sold' => 0
];

$insert_id = $db->create("users", $data);

if ($insert_id) {
  echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
} else {
  echo json_encode(['status' => 'fail', 'message' => 'DB insert failed']);
}


