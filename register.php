<?php
  require_once "db_connection.php";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name  = trim($_POST["last_name"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $username   = trim($_POST["username"] ?? "");
    $password   = $_POST["password"] ?? "";
    $confirm_pass = $_POST["confirm_password"] ?? "";

    if ($password !== $confirm_pass) {
      die("Error: Passwords do not match.");
    }

    if (strlen($password) < 8) {
      die("Error: Password must be at least 8 characters long.");
    }

    $check_query = "SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1";
    if ($stmt = $conn->prepare($check_query)) {
      $stmt->bind_param("ss", $username, $email);
      $stmt->execute();
      $result = $stmt->get_result();
      $user = $result->fetch_assoc();
      $stmt->close();
    } else {
      die("Error: Failed to prepare query.");
    }

    if ($user) {
      if ($user["username"] === $username) {
        die("Error: Username already exists.");
      }
      if ($user["email"] === $email) {
        die("Error: Email already exists.");
      }
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, email, username, password) VALUES (?, ?, ?, ?, ?)";
    if ($insert_stmt = $conn->prepare($sql)) {
      $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $username, $hashed_password);
      if ($insert_stmt->execute()) {
        header("Location: login_page.html?signup=success");
        exit();
      } else {
        echo "Error: " . $conn->error;
      }
      $insert_stmt->close();
    } else {
      echo "Error: Failed to prepare insert statement.";
    }
  }

  $conn->close();
?>
