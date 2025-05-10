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

if (empty($_POST['phone']) || empty($_POST['password'])) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Email and password are required',
  ]);
  exit; // Exit to ensure no further code is executed
}

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/auth.php";

$phone = $_POST['phone'];
$password = $_POST['password'];

$authSystem = new AuthSystem();
$authSystem->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);

$jwt = $authSystem->login($phone, $password);

if ($jwt) {
  $profile_data = $authSystem->read("users", "phone_number", $phone);
  $data = $profile_data[0];
  echo json_encode([
    'status' => 'success',
    'message' => 'Login successful',
    'remaining_margin' => get_total_paid_purchase_amount($authSystem, $phone),
    'token' => $jwt,
    'data' => $data
  ]);
} else {
  // Invalid credentials or not verified
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid email/password or unverified user',
  ]);
}


function get_total_paid_purchase_amount($db, $phone_number) {
    $totalAmount = 0;
    $paidPurchases = $db->read1('user_purchases', 'status', 'phone_number', 1, $phone_number);

    if ($paidPurchases && is_array($paidPurchases)) {
        foreach ($paidPurchases as $purchase) {
            $purchaseId = $purchase['id'];
            $details = $db->read('user_purchase_details', 'purchase_id', $purchaseId);

            if ($details && is_array($details)) {
                foreach ($details as $detail) {
                    $totalAmount += (int) $detail['item_total'];
                }
            }
        }
    }

    return $totalAmount;
}
