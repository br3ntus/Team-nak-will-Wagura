<?php
// We need to connect to the database first
require_once "db_connection.php";

// We start a session so the server can remember who is logged in as they browse
session_start();

// This part only runs if the user submitted the login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // We grab the username and password they entered
  $username_input = mysqli_real_escape_string($conn, $_POST["username"]);
  $password_input = $_POST["password"];

  // We look for a user that has that username or email
  $sql = "SELECT * FROM users WHERE username = '$username_input' OR email = '$username_input' LIMIT 1";
  $result = mysqli_query($conn, $sql);
  $user = mysqli_fetch_assoc($result);

  if ($user) {
    // If we find a user, we check if the password matches the scrambled version in our database
    if (password_verify($password_input, $user["password"])) {

      // If it matches, we save their info in the session
      $_SESSION["user_id"] = $user["user_id"];
      $_SESSION["username"] = $user["username"];
      $_SESSION["first_name"] = $user["first_name"];

      // And then we take them straight to their dashboard
      header("Location: user/dashboard_page.html");
      exit();
    } else {
      // If the password is wrong, we send them back to the login page with an error
      header("Location: login_page.html?error=invalid_credentials");
      exit();
    }
  } else {
    // If we can't find that user at all, we show the same error for security
    header("Location: login_page.html?error=invalid_credentials");
    exit();
  }
}

// Always close the connection when we're done
mysqli_close($conn);
