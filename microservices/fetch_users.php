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
    $sql = "SELECT id, username, business_name, phone_number, address, front_image, qr_image, joining_date FROM users ORDER BY id DESC";
    $stmt = $db->db_handler->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $users
    ]);
} catch (PDOException $e) {
    $db->log("Error fetching users: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch users."
    ]);
}
?>
