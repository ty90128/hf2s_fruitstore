<?php
// 設定內容類型為 JSON
header('Content-Type: application/json');

// 開啟 session
session_start();

// 檢查是否設置了 user_id
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '無效的用戶 ID']);
    exit;
}
$user_id = $_SESSION['user_id'];  // 取得使用者 ID

// 接收並解析 JSON 資料
$rawInput = file_get_contents('php://input');  // 讀取原始輸入資料
error_log($rawInput);  // 將原始資料記錄到伺服器的錯誤日誌中，方便排錯

$input = json_decode($rawInput, true);  // 解析 JSON 資料

// 檢查 json_decode 是否成功
if ($input === null) {
    echo json_encode(['success' => false, 'error' => '無法解析傳入的 JSON 資料']);
    exit;
}

// 檢查是否有 'name' 欄位
if (!isset($input['name'])) {
    echo json_encode(['success' => false, 'error' => '缺少商品名稱']);
    exit;
}

$productname = $input['name'];  // 取得商品名稱

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

// 使用商品名稱和 user_id 刪除資料
$sql = "DELETE FROM `add` WHERE pname = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $productname, $user_id);  // 綁定字串型別的參數
$stmt->execute();  // 執行刪除操作

// 查詢該使用者的剩餘商品
$sql = "SELECT * FROM `add` WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);  // 綁定使用者 ID
$stmt->execute();

$result = $stmt->get_result();
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

// 返回操作結果
if (empty($cartItems)) {
    // 當購物車內沒有任何商品時，返回空陣列，表示購物車為空
    echo json_encode(['success' => true, 'cartItems' => []]); 
    exit;
}

// 返回剩餘商品的資訊
echo json_encode(['success' => true, 'cartItems' => $cartItems]);

$conn->close();
?>