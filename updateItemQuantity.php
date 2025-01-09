<?php
// updateItemQuantity.php
header('Content-Type: application/json');

// 資料庫連接設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hf2s_fruitstore";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連接
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗: ' . $conn->connect_error]));
}

// 啟用 session
session_start();

// 檢查是否有已登入的 user_id，否則生成隨機 user_id
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('guest_');
}
$user_id = $_SESSION['user_id'];

// 接收 JSON 請求資料
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_name']) || !isset($data['amount'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要的參數']);
    exit;
}

$product_name = $data['product_name'];
$new_amount = $data['amount'];

// 檢查參數是否有效
if (!is_string($product_name) || !is_numeric($new_amount) || $new_amount < 1) {
    echo json_encode(['success' => false, 'message' => '無效的數據']);
    exit;
}

// 更新購物車數量
$stmt = $conn->prepare("UPDATE `add` SET amount = ? WHERE pname = ? AND user_id = ?");
$stmt->bind_param("iss", $new_amount, $product_name, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // 查詢更新後的購物車內容
    $stmt = $conn->prepare("SELECT * FROM `add` WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'cartItems' => $cartItems]);
} else {
    echo json_encode(['success' => false, 'message' => '更新失敗或數據未改變']);
}

if (!isset($data['product_name']) || !isset($data['amount'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要的參數']);
    exit;
}

// 關閉資料庫連線
$stmt->close();
$conn->close();
?>
