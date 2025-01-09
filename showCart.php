<?php

header('Content-Type: application/json');

// 資料庫連接設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hf2s_fruitstore";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連接
if ($conn->connect_error) {
    die("資料庫連接失敗: " . $conn->connect_error);
}

// 啟用 session
session_start();

// 檢查是否有已登入的 user_id，否則生成隨機 user_id
if (!isset($_SESSION['user_id'])) {
    // 生成唯一的 user_id（如 UUID 或隨機數字）
    $_SESSION['user_id'] = uniqid('guest_');
}
$user_id = $_SESSION['user_id'];

// 從資料庫抓取購物車資料
$sql = "SELECT * FROM `add` WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id); //  "i" 因為 user_id 是整數
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
// 檢查是否從資料庫取得資料
if (empty($orders)) {
    echo json_encode([]); // 返回空陣列，表示沒有購物車項目
    exit;
}

$stmt->close();
$conn->close();

// 將資料以 JSON 格式返回，設定編碼為 UTF-8
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
