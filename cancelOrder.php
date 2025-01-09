<?php
header('Content-Type: application/json');

// 資料庫連接
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hf2s_fruitstore";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log('資料庫連接失敗: ' . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

session_start();

// 獲取 POST 數據
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    error_log('無法解析 JSON 數據');
    echo json_encode(['success' => false, 'message' => '無法解析 JSON 數據']);
    exit;
}

$order_id = $data['order_id'] ?? null;
$user_id = $data['user_id'] ?? $_SESSION['user_id'] ?? null;

if (!$order_id || !$user_id) {
    error_log('缺少必要的參數');
    echo json_encode(['success' => false, 'message' => '缺少必要的參數']);
    exit;
}

// 直接更新訂單
$sql = "UPDATE `order` SET cancel = '已取消(審核中)' WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log('SQL準備失敗: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'SQL準備失敗']);
    exit;
}
$stmt->bind_param("is", $order_id, $user_id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '更新失敗或找不到匹配的訂單']);
}
$stmt->close();
$conn->close();
?>
