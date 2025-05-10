<?php
require_once '../database_system/database.php'; // Adjust path if needed
require_once '../config.php'; // Adjust path if needed


$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD); // update credentials

if (!$db->db_handler) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);
    exit;
}

try {
$phone_number = $_POST['phone_number'];
  $user = $db->read("users", "phone_number", $phone_number);

    echo json_encode([
        "status" => "success",
        "data" => $user,
        "margin_remaining" => get_total_paid_purchase_amount($db, $phone_number)
    ]);
} catch (PDOException $e) {
    $db->log("Error fetching users: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch users."
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




?>
