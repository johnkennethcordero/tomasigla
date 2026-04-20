<?php
$host     = "roundhouse.proxy.rlwy.net";
$port     = "41810";
$dbname   = "railway";
$username = "root";
$password = "qWuJmJEuZXbMphMiTPCfyfoBPnLSCPOy";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB error: " . $e->getMessage()]);
    exit;
}
?>