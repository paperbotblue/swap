<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . '/mail_system.php';

// Check for POST request and required fields
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validate fields
    if (empty($name) || empty($subject) || empty($email) || empty($message)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Required fields are missing',
        ]);
        exit;
    }

    $body = "
        <h1>New Contact Form Submission</h1>
        <p><strong>Name:</strong> $name</p>
        <p><strong>subject:</strong> $subject</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Message:</strong></p>
        <p>$message</p>
    ";
    $altBody = "New Contact Form Submission:\nName: $name\nSubject: $subject\nEmail: $email\nMessage: $message";

    $mailer = new Mailer();
    $result = $mailer->sendEmail(
      'no-reply@versai.in',   // From Email
      'DNR website',                // From Name
      'paperbotblue@gmail.com',    // To Email
      'Versai',           // To Name
      'Mail from DNR',      // Subject
      $body,
      $altBody
    );

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Email sent successfully',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => $result,
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method',
    ]);
}
?>
