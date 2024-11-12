<?php
require_once __DIR__ . '/../itelec-2/database/dbconnection.php';
include_once __DIR__ . '/../itelec-2/config/settings-configuration.php';

$token = filter_input(INPUT_GET, 'token');


$token = trim($_POST["token"]);
$token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
$token_hash = hash("sha256", $token);

$database = new Database();
$mysqli = $database->dbConnection(); 

$sql = "SELECT * FROM user WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->execute([$token_hash]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user === false) {
    die("Token not found.");
}

$current_time = time();
$expiry_time = strtotime($user["reset_token_expires_at"]);

if ($expiry_time <= $current_time) {
    echo "Current time: " . date("Y-m-d H:i:s", $current_time) . "\n";
    echo "Token expiry time: " . date("Y-m-d H:i:s", $expiry_time) . "\n";
    die("Token has expired.");
}

// Validate the new password
if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match.");
}

// Hash the new password
$password_hash = md5($_POST["password"]); // Use md5 for hashing

// Prepare the update SQL statement
$sql = "UPDATE user
        SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL
        WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->execute([$password_hash, $user["id"]]);

echo "<script>alert('Password updated. You can now login.'); window.location.href = 'http://localhost/itelec2-main/';</script>";

?>
