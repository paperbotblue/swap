<?php

require_once __DIR__ . "/../database_system/database.php";
require_once __DIR__ . "/../config.php";

$db = new Database();
$db->new($DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);
// user
$columns = [
  'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
  'username' => 'VARCHAR(20) NOT NULL',
  'business_name' => 'VARCHAR(20) NOT NULL', 
  'phone_number' => 'VARCHAR(10) NOT NULL UNIQUE',
  'password' => 'VARCHAR(255) NOT NULL',
  'total_sold' => 'INT(11) NOT NULL',
  'total_margin_remaining' => 'INT(11) NOT NULL',
  'total_items_sold' => 'INT(11) NOT NULL',
  'front_image' => 'VARCHAR(255) NOT NULL',
  'qr_image' => 'VARCHAR(255) NOT NULL',
  'address' => 'VARCHAR(20) NOT NULL', 
  'joining_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
  'is_admin' => 'TINYINT(1) NOT NULL DEFAULT 0',
];

$db->create_table('users', $columns); 
// sales
$columns = [
  'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
  'phone_number' => 'VARCHAR(10) NOT NULL UNIQUE',
  'item_name' => 'VARCHAR(20) NOT NULL',
  'item_category' => 'VARCHAR(20) NOT NULL',
  'item_quantity' => 'INT(10)',
  'regisDate' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
];

$db->create_table('user_sales', $columns);

// money transfered
$columns = [
  'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
  'phone_number' => 'VARCHAR(10) NOT NULL UNIQUE',
  'date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
  'amount' => 'INT(10)',
  'screenshot_src' => 'VARCHAR(255) NOT NULL'
];

$db->create_table('payment_history', $columns);

// money request
$columns = [
  'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
  'phone_number' => 'VARCHAR(10) NOT NULL UNIQUE',
  'user_name' => 'VARCHAR(20) NOT NULL',
  'amount' => 'INT(10)',
  'is_sent' => 'TINYINT(1) NOT NULL DEFAULT 0',
  'delay_payment' => 'TINYINT(1) NOT NULL DEFAULT 0'
];

$db->create_table('payment_request', $columns);

$columns = [
  'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
  'phone_number' => 'VARCHAR(10) NOT NULL UNIQUE',
  'date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
  'status' => 'TINYINT(1)',
  'amount' => 'INT(11) NOT NULL'
];

$db->create_table('user_purchases', $columns);

$columns = [
  'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
  'purchase_id' => 'INT(11) NOT NULL',
  'item_name' => 'VARCHAR(20) NOT NULL',
  'item_category' => 'VARCHAR(20) NOT NULL',
  'item_quantity' => 'INT(10)',
  'regisDate' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
  // Optional: Add FOREIGN KEY constraint if using InnoDB
];

$db->create_table('user_purchase_details', $columns);


echo json_encode(["status" => "success"]);

/* $columns = [ */
/*     'email' => 'VARCHAR(255) NOT NULL PRIMARY KEY', */
/*     'course_1' => 'TINYINT(1) NOT NULL DEFAULT 0', */
/*     'course_2' => 'TINYINT(1) NOT NULL DEFAULT 0', */
/*     'course_3' => 'TINYINT(1) NOT NULL DEFAULT 0', */
/*     'course_4' => 'TINYINT(1) NOT NULL DEFAULT 0' */
/* ]; */
/* $db->create_table('user_courses', $columns); */
/**/
/* $sql = "ALTER TABLE user_courses ADD CONSTRAINT fk_user_courses FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE ON UPDATE CASCADE;"; */
/* $db->db_handler->exec($sql); */
/**/
/**/
/* $columns = [ */
/*     'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY', */
/*     'chapter_no' => 'INT(11) NOT NULL', */
/*     'question_no' => 'INT(11) NOT NULL', */
/*     'question' => 'TEXT NOT NULL', */
/*     'option_1' => 'VARCHAR(255) NOT NULL', */
/*     'option_2' => 'VARCHAR(255) NOT NULL', */
/*     'option_3' => 'VARCHAR(255) NOT NULL', */
/*     'option_4' => 'VARCHAR(255) NOT NULL', */
/*     'correct_answer' => 'VARCHAR(255) NOT NULL' */
/* ]; */
/* $db->create_table('quiz_questions', $columns); */
/**/

