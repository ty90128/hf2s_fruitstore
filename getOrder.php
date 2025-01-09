<?php
header('Content-Type: application/json');

session_start();
// 資料庫連線設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hf2s_fruitstore";

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗: ' . $conn->connect_error]));
}

// 接收 JSON 請求資料
$inputData = json_decode(file_get_contents('php://input'), true);
if (!isset($inputData['user_id']) || !is_numeric($inputData['user_id']) || $inputData['user_id'] <= 0) {
    echo json_encode(['success' => false, 'message' => '無效的用戶 ID']);
    exit;
}


if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'JSON 解碼錯誤: ' . json_last_error_msg()]);
    exit;
}

// 確認資料是否存在
if (!isset($inputData['user_id']) || !isset($inputData['personDetails']) || !isset($inputData['orderDetails'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要的資料']);
    exit;
}

$user_id = $inputData['user_id'];
$personDetails = $inputData['personDetails'];
$orderDetails = $inputData['orderDetails'];
$totalAmount = $inputData['total']; // 從前端接收總金額

// 檢查 orderDetails 是否為陣列
if (!is_array($orderDetails) || empty($orderDetails)) {
    echo json_encode(['success' => false, 'message' => '訂單明細錯誤或為空']);
    exit;
}

// 開始交易
$conn->begin_transaction();

try {
    // 處理取貨人資料並插入 guest_detail
    $guestDetail = "\"{$personDetails['name']}\",\"{$personDetails['phone']}\",\"{$personDetails['email']}\",\"{$personDetails['address']}\""; // 去除 Unicode 編碼，直接顯示中文

    // 處理商品詳細資料並插入 p_dtail
    $pDetail = json_encode(array_map(function($item) {
        return "{$item['pname']} NT\${$item['price']}*{$item['amount']}盒";
    }, $orderDetails), JSON_UNESCAPED_UNICODE);

    // 插入 order 資料
    $stmt = $conn->prepare("INSERT INTO `order` (user_id, guest_detail, p_detail, total, ts, pay, state, refund, cancel) VALUES (?, ?, ?, ?, NOW(), '待付款', '未出貨', '未退貨/款','未取消')");
    $stmt->bind_param("isss", $user_id, $guestDetail, $pDetail, $totalAmount);
    
    // 執行語句
    if (!$stmt->execute()) {
        throw new Exception("SQL 執行錯誤: " . $stmt->error);
    }

    // 清空該用戶的購物車資料（假設購物車資料存在某個表格中）
    $stmtClearCart = $conn->prepare("DELETE FROM `add` WHERE user_id = ?");
    $stmtClearCart->bind_param("i", $user_id);
    if (!$stmtClearCart->execute()) {
        error_log("Failed to clear cart: " . $stmtClearCart->error);
    }


    // 提交交易
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // 發生錯誤時回滾交易
    $conn->rollback();
    error_log("Received data: " . var_export($inputData, true));  // 打印接收到的数据
    error_log("SQL Error: " . $conn->error);  // 打印 SQL 错误

    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// 關閉連接
$stmt->close();
$conn->close();

?>
