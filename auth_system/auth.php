<?php 

require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . "/../database_system/database.php";
require_once __DIR__ . "/../microservices/mail_system.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

class AuthSystem extends Database{
  private $jwt_secret = 'askdfjasjnpv20q3947p0vp3ashldf';

  public function register($email, $password) {
    $result = $this->read("users", "email", $email); 
    if (count($result) > 0) {
      return false;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $data = [
      "email" => $email,
      "password" => $hashedPassword, 
      "is_verified" => 0,
      "verification_token" => "",
      "verification_token_ttl" => time() + 300 
    ];

    $this->create("users", $data);
    $this->sendVerificationEmail($email);
  }

  public function sendVerificationEmail($email) {
    $token = bin2hex(random_bytes(50));
    $this->update("users", "email", $email, "verification_token", $token);

    $verificationLink = "http://zivaworld.online/auth_system/verify.php?token=$token";

    $mailer = new Mailer();

    $mailer->sendEmail(
      'no-reply@versai.in',
      'Your Name',
      $email,
      'Recipient Name',
      'Here is the subject',
      'This is the HTML message body  <b>in bold!</b>' . $verificationLink,
      'This is the plain text version of the message body'
    );
  }

  public function sendVerificationEmailPasswdChange($email) {
    $token = bin2hex(random_bytes(30));

    $this->update("users", "email", $email, "unlock_token", $token);
    $this->update("users", "email", $email, "unlock_token_ttl", time() + 300);

    $verificationLink = "http://localhost:3000/php_systems/auth_system/unlock_user.php?token=$token";
    $mailer = new Mailer();

    $mailer->sendEmail(
      'no-reply@versai.in',   
      'Your Name',                
      $email,
      'Recipient Name',           
      'Here is the subject',     
      'This is the HTML message body  <b>in bold!</b>' . $verificationLink, 
      'This is the plain text version of the message body'
    );
  }

  public function sendPasswordToMail($email, $password) {
    $mailer = new Mailer();
    $mailer->sendEmail(
      'no-reply@versai.in',  
      'Your Name',                
      $email,
      'Recipient Name',           
      'Here is the subject',     
      'Your new password is <b>password</b>: ' . $password, 
      'This is the plain text version of the message body'
    );
  }
  
  public function login($phone_number, $password) {
    $result = $this->read("users", "phone_number", $phone_number);
    if (count($result) == 0) {
      return false;
    }

    $storedPassword = $result[0]['password'];
    if (!password_verify($password, $storedPassword)) {
      return false;
    }

    $payload = [
      'sub' => $result[0]['id'],
      'phone' => $result[0]['phone_number'],
      'iat' => time(),
      'exp' => time() + 360000000,
    ];

   
    $jwt = JWT::encode($payload, $this->jwt_secret, 'HS256');
    return $jwt;
  }
  
  
public function changePassword($token, $oldPassword, $newPassword) {
    try {
        // Validate token format
        if (!$token || substr_count($token, '.') !== 2) {
            return [
                "status" => "error",
                "message" => "Invalid token format."
            ];
        }

        // Decode token
        $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
        $userId = $decoded->sub ?? null;

        if (!$userId) {
            return [
                "status" => "error",
                "message" => "Invalid token payload."
            ];
        }

        // Fetch user data
        $result = $this->read("users", "id", $userId);
        if (!$result || count($result) == 0) {
            return [
                "status" => "error",
                "message" => "User not found."
            ];
        }

        $user = $result[0];
        $storedPassword = $user['password'];

        // Verify old password
        if (!password_verify($oldPassword, $storedPassword)) {
            return [
                "status" => "error",
                "message" => "Old password is incorrect."
            ];
        }

        // Hash and update the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->update("users", "id", $userId, "password", $hashedPassword);

        return [
            "status" => "success",
            "message" => "Password changed successfully."
        ];

    } catch (ExpiredException $e) {
        return ["status" => "error", "message" => "Token has expired. Please log in again."];
    } catch (SignatureInvalidException | BeforeValidException $e) {
        return ["status" => "error", "message" => "Invalid or tampered token."];
    } catch (Exception $e) {
        return ["status" => "error", "message" => "Error: " . $e->getMessage()];
    }
}

  public function get_phone_number_fron_token($token) {
    $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
    $userId = $decoded->sub; 

    $result = $this->read("users", "id", $userId);
    if (count($result) == 0) {
      return [
        "status" => "error",
        "message" => "user data not found"
      ];
    }
    if (isset($decoded_array['exp']) && $decoded_array['exp'] < time()) {
      return [
        "status" => "error",
        "message" => "Token has expired."
      ];
    }
    return $result[0]['phone_number']; 
  }

public function token_validation($token) {
    try {
        // Decode the token
        $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
        $userId = $decoded->sub ?? null; 

        if (!$userId) {
            return ["status" => "error", "message" => "Invalid token payload."];
        }

        // Fetch user data
        $user = $this->read("users", "id", $userId);
        if (!$user) {
            return ["status" => "error", "message" => "User data not found."];
        }

        // Fetch user profile
        $user_profile = $this->read("users", "id", $userId);
        if (!$user_profile) {
            return ["status" => "error", "message" => "User profile not found." . $userId];
        }

        return ["status" => "success", "message" => "User found", "data" => $user_profile[0]];

    } catch (ExpiredException $e) {
        return ["status" => "error", "message" => "Token has expired. Please log in again."];
    } catch (SignatureInvalidException | BeforeValidException $e) {
        return ["status" => "error", "message" => "Invalid or tampered token."];
    } catch (Exception $e) {
        return ["status" => "error", "message" => "Authentication error: " . $e->getMessage()];
    }
}
}


