<?php

header('Content-Type: application/json');

// 連接資料庫
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hf2s_fruitstore";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連接是否成功
if ($conn->connect_error) {
    die("資料庫連接失敗: " . $conn->connect_error);
}

session_start();

// 檢查是否有有效的使用者 ID
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('guest_');  // 若無，則使用一個唯一的訪客 ID
}
$user_id = $_SESSION['user_id'];  // 取得使用者 ID

// 刪除該使用者所有的商品
$sql = "DELETE FROM `add` WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();

// 返回成功
echo json_encode(['success' => true]);
?>
