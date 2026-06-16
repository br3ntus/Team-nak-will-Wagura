<?php
  // Here's where we set up the database info so PHP knows where to look
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "wagura_db";

  // This part actually tries to open the door to the database
  $conn = new mysqli($servername, $username, $password, $dbname);

  // If the door is locked or broken, this stops everything and tells us why
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
?>
