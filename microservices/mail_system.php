<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  private $mail;

  public function __construct() {
    $this->mail = new PHPMailer(true);
    global $MAIL_HOST;
    global $MAIL_USER;
    global $MAIL_PASSWD;
    
    $host = $MAIL_HOST; 
    $username = $MAIL_USER;
    $password = $MAIL_PASSWD;
    $port = 587;
    $encryption = PHPMailer::ENCRYPTION_STARTTLS;

    try {
      // Server settings
      $this->mail->isSMTP();                                  // Send using SMTP
      $this->mail->Host       = $host;                        // Set the SMTP server
      $this->mail->SMTPAuth   = true;                         // Enable SMTP authentication
      $this->mail->Username   = $username;                    // SMTP username
      $this->mail->Password   = $password;                    // SMTP password
      $this->mail->SMTPSecure = $encryption;                  // Enable TLS encryption
      $this->mail->Port       = $port;                        // TCP port to connect to
    } catch (Exception $e) {
      echo "Mailer setup error: {$e->getMessage()}";
    }
  }

  public function sendEmail($fromEmail, $fromName, $toEmail, $toName, $subject, $body, $altBody = ''): bool {
    try {
      // Recipients
      $this->mail->setFrom($fromEmail, $fromName);
      $this->mail->addAddress($toEmail, $toName);             // Add a recipient

      // Content
      $this->mail->isHTML(true);                              // Set email format to HTML
      $this->mail->Subject = $subject;
      $this->mail->Body    = $body;
      $this->mail->AltBody = $altBody;

      $this->mail->send();
      return true;
    } catch (Exception $e) {
      error_log("unable to send mail" . $e->getMessage(), 3, __DIR__ . "/errors.log");
      return false;
    }
  }
}

