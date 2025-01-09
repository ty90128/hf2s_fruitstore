<?php

// 設定內容類型為 JSON
header('Content-Type: application/json');

// 資料庫連接設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hf2s_fruitstore";

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die(json_encode(["error" => "連接資料庫失敗: " . $conn->connect_error]));
}

// 啟用 session
session_start();

$user_id = $_SESSION['user_id'];

// 查詢訂單資料
$sql = "SELECT id, p_detail, total, pay, state, refund, cancel FROM `order` WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 檢查是否有資料
if ($result->num_rows > 0) {
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $product_details = json_decode($row['p_detail'], true);
        $products = [];

        if (json_last_error() === JSON_ERROR_NONE && is_array($product_details)) {
            foreach ($product_details as $product_detail) {
                if (preg_match('/(.+?) NT\$(\d+)\*(\d+)盒/', $product_detail, $matches)) {
                    $products[] = [
                        "product_name" => $matches[1],
                        "quantity" => (int)$matches[3],
                        "amount" => (int)$matches[2] * (int)$matches[3]
                    ];
                }
            }
        } else {
            echo json_encode(["error" => "解析 p_detail 失敗"]);
            exit;
        }

        $orders[] = [
            'order_id' => $row['id'],
            'user_id' => $user_id,
            "products" => $products,
            "total" => $row['total'],
            "pay" => $row['pay'],
            "status" => $row['state'],
            "cancel" => $row['cancel'],
            "refund" => $row['refund']
        ];
    }
    echo json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode(["message" => "目前無訂單"]);
}

$conn->close();
?>