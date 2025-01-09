<?php
// 接收並解析 JSON 資料
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// 檢查 json_decode 是否成功
if ($input === null) {
    echo json_encode(['success' => false, 'error' => '無法解析傳入的 JSON 資料']);
    exit;
}

// 檢查是否有必要的欄位
if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'error' => '缺少必要的欄位']);
    exit;
}

$product_id = $input['product_id'];  // 取得商品 ID
$quantity = (int) $input['quantity'];  // 取得數量並轉換為整數

// 檢查數量是否合法
if ($quantity < 0 || $quantity > 10) {
    echo json_encode(['success' => false, 'error' => '數量應該在 0 到 10 之間']);
    exit;
}

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

// 確保有有效的 user_id
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('guest_');
}
$user_id = $_SESSION['user_id'];  // 取得使用者 ID

// 更新該商品的數量
$sql = "UPDATE `add` SET quantity = ? WHERE product_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $quantity, $product_id, $user_id);  // 綁定數量、商品 ID 和使用者 ID
$stmt->execute();

// 檢查更新是否成功
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => '更新失敗']);
}

$stmt->close();
$conn->close();
?>
