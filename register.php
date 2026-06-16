<?php
  // First, we bring in the database connection so we can talk to it
  require_once "db_connection.php";

  // We only want this code to run if someone actually hit the submit button
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Here we grab everything the user typed in the form
    // We use mysqli_real_escape_string so nobody can mess with our database using weird characters
    $first_name = mysqli_real_escape_string($conn, $_POST["first_name"]);
    $last_name  = mysqli_real_escape_string($conn, $_POST["last_name"]);
    $email      = mysqli_real_escape_string($conn, $_POST["email"]);
    $username   = mysqli_real_escape_string($conn, $_POST["username"]);
    $password   = $_POST["password"];
    $confirm_pass = $_POST["confirm_password"];

    // We check if the passwords match and if they're long enough
    // This keeps the account secure
    if ($password !== $confirm_pass) {
      die("Error: Passwords do not match.");
    }

    if (strlen($password) < 8) {
      die("Error: Password must be at least 8 characters long.");
    }

    // Now we check if someone already used that username or email
    // We don't want duplicates in our system
    $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
      if ($user["username"] === $username) {
        die("Error: Username already exists.");
      }
      if ($user["email"] === $email) {
        die("Error: Email already exists.");
      }
    }

    // Here's the important part. We scramble the password before saving it.
    // This way, if someone steals the data, they still can't see the real password.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Finally, we put all the info into the users table
    $sql = "INSERT INTO users (first_name, last_name, email, username, password) 
            VALUES ('$first_name', '$last_name', '$email', '$username', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
      // If it worked, we send them to the login page so they can start using the app
      header("Location: login_page.html?signup=success");
      exit();
    } else {
      // If something went wrong, we show the error so we can fix it
      echo "Error: " . mysqli_error($conn);
    }
  }

  // Close the connection when we're done
  mysqli_close($conn);
?>
