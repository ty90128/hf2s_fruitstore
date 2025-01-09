<?php
session_start();  // 啟動 session
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// 確保收到的數據是 JSON 格式
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['token'])) {
    echo json_encode(["error" => "Token is required"]);
    exit;
}

// 使用 token 或 user_id 查詢用戶資料
$servername = "localhost";
$username = "root";
$passwordDB = "";
$dbname = "hf2s_fruitstore";

// 建立資料庫連接
$conn = new mysqli($servername, $username, $passwordDB, $dbname);

// 檢查資料庫連接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($data['token'])) {
    $token = $data['token'];
    $sql = "SELECT * FROM register WHERE token = '$token'";  // 使用 token 查詢
} 

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        "name" => $user['name'],
        "phone" => $user['phone'],
        "email" => $user['email'],
        "address" => $user['address']
    ]);
} else {
    echo json_encode(["error" => "User not found"]);
}

$conn->close();

?>
