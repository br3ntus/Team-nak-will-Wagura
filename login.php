<?php
require_once "db_connection_pdo.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username_input = trim($_POST["username"] ?? "");
    $password_input = $_POST["password"] ?? "";

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :login OR email = :login LIMIT 1");
        $stmt->execute(['login' => $username_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password_input, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["first_name"] = $user["first_name"];

            header("Location: user/dashboard_page.php");
            exit();
        }
    } catch (PDOException $e) {
        // Log or handle the exception if needed.
    }

    header("Location: login_page.html?error=invalid_credentials");
    exit();
}
