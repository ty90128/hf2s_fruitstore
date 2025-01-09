<?php
session_start();  // 啟動 session

// 設置內容類型為 JSON
header('Content-Type: application/json');
// 允許跨域請求
header('Access-Control-Allow-Origin: *');
// 允許 POST 方法
header('Access-Control-Allow-Methods: POST');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'root', '', 'hf2s_fruitstore');
    if ($conn->connect_error) {
        die('資料庫連接錯誤');
    }

    $sql = "SELECT * FROM register WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {  // 如果查詢成功且結果中有至少一筆資料
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id']; // 儲存 user_id 到 session
            $token = bin2hex(random_bytes(16)); // 生成一個隨機 token
             // 更新資料庫中的 token
            $updateToken = "UPDATE register SET token = '$token' WHERE email = '$email'";

            if ($conn->query($updateToken) === TRUE) {
                // 返回成功響應，包含 user_id 和 token
                echo json_encode([
                    "success" => "User logged in", 
                    "token" => $token, 
                    "user_id" => $user['user_id']
                ]);
            }
        } else {
            echo json_encode(["error" => "Invalid email or password"]);
        }
    } else {
        echo json_encode(["error" => "Invalid email or password"]);
    }
    $conn->close();
}
?>
