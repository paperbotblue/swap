<?php

class Database {
  public $db_handler;
  private $error_file_path;


  public function new($db_name, $db_user, $db_passwd) {
    $this->error_file_path = __DIR__ . "/errors.log";

    try {
      $this->db_handler = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_passwd);
      $this->db_handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
      $this->log("unable to connect to db " . $e->getMessage());
      $this->db_handler = null; 
    }
  }


  public function create_db($db_name) {
    try {
      $sql = "CREATE DATABASE `$db_name`";
      $this->db_handler->exec($sql);
      return true;

    } catch (PDOException $e) {
      $this->log("Failed to create database $db_name: " . $e->getMessage());
      return false;
    }
  }

  public function create_table($table_name, $columns) {
    $columnDefinitions = [];
    foreach ($columns as $columnName => $definition) {
      $columnDefinitions[] = "`$columnName` $definition";
    }
    $columnsSql = implode(", ", $columnDefinitions);
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` ($columnsSql);";

    try {
      $this->db_handler->exec($sql);
      return true; 

    } catch (PDOException $e) {
      $this->log("Unable to create table $table_name: " . $e->getMessage() . PHP_EOL, 3, $this->error_file_path);
      return false; 
    }
  }


  public function create($table_name, $data) {
    $columns = implode(", ", array_keys($data)); 
    $placeholders = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table_name ($columns) VALUES ($placeholders)";

    try {
      $stmt = $this->db_handler->prepare($sql);
      foreach ($data as $key => $value) {
        $stmt->bindParam(":$key", $data[$key]);
      }
      $stmt->execute();
      return $this->db_handler->lastInsertId();

    } catch (PDOException $e) {
      $this->log("Error inserting into $table_name: " . $e->getMessage());
      return false;
    }
  }



  public function read($table_name, $attribute, $value) {
    try {
      $sql = "SELECT * FROM $table_name WHERE $attribute = :value";
      $stmt = $this->db_handler->prepare($sql);
      $stmt->bindParam(":value", $value);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $result;

    } catch (PDOException $e) {
      $this->log("Error executing read query: " . $e->getMessage());
      return false;     
    }
  }

  public function read1($table_name, $attribute, $attribute2, $value, $value2) {
    try {
      $sql = "SELECT * FROM $table_name WHERE $attribute = :value AND $attribute2 = :value2";
      $stmt = $this->db_handler->prepare($sql);
      $stmt->bindParam(":value", $value);
      $stmt->bindParam(":value2", $value2);

      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $result;

    } catch (PDOException $e) {
      $this->log("Error executing read query: " . $e->getMessage());
      return false;     
    }
  }
  public function read_all($table_name) {
    try {
      $sql = "SELECT * FROM $table_name";
      $stmt = $this->db_handler->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      $this->log("Error fetching all from $table_name: " . $e->getMessage());
      return false;
    }
  }




  public function update($table_name, $attribute, $value, $attribute_c, $value_c) {
    try {
      $sql = "UPDATE $table_name SET $attribute_c = :value_c WHERE $attribute = :value";
      $stmt = $this->db_handler->prepare($sql);
      $stmt->bindParam(':value', $value);
      $stmt->bindParam(':value_c', $value_c);
      if ($stmt->execute()) {
        return true;
      } else {
        return false;
      }

    } catch (PDOException $e) {
      $this->log("Failed to update $table_name: " . $e->getMessage());
      return false;
    }
  }
  
public function update_many($table_name, $email, $data) {
    $data = array_filter($data, function($value) {
        return $value !== null && $value !== '';
    });

    $setPart = [];
    foreach ($data as $column => $value) {
        $setPart[] = "$column = :$column"; 
    }
    $sql = "UPDATE $table_name SET " . implode(", ", $setPart) . " WHERE email = :email";
    $data['email'] = $email;

    try {
        $stmt = $this->db_handler->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindParam(":$key", $data[$key]);
        }
        $stmt->execute();
        return true;

    } catch (PDOException $e) {
        $this->log("Error updating $table_name: " . $e->getMessage());
        return false;
    }
}



  public function delete($table_name, $attribute, $value) {
    try {
      $sql = "DELETE FROM $table_name WHERE $attribute = :value";
      $stmt = $this->db_handler->prepare($sql);
      $stmt->bindParam(':value', $value);
      $stmt->execute();
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      $this->log("Error deleting from $table_name: " . $e->getMessage());
      return false;
    }
  }


  public function stmt_execute($stmt, $error_message) {
    if(!$stmt->execute()) {
      error_log($error_message. PHP_EOL, 3, $this->error_file_path);
      return false;
    }
    $stmt->close();
    return true;
  }

  public function log($log) {
    error_log( $log . PHP_EOL, 3, $this->error_file_path);
  }
}
