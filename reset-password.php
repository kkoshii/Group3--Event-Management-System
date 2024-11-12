<?php
session_start();

require_once __DIR__ . '/../itelec-2/database/dbconnection.php';

$token = filter_input(INPUT_GET, 'token');

if (!$token) {
    die("Invalid token.");
}

$token_hash = hash("sha256", $token);

$database = new Database();
$mysqli = $database->dbConnection(); 

$sql = "SELECT * FROM user WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);

$stmt->bindValue(1, $token_hash, PDO::PARAM_STR);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result === false) {
    die("Token not found.");
}

if (strtotime($result["reset_token_expires_at"]) <= time()) {
    die("Token has expired.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Reset Password</h1>
    <form method="post" action="process-reset-password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirmation">Repeat Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
