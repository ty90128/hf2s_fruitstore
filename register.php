<?php
header('Content-Type: application/json'); // 設定回應為 JSON 格式

// 資料庫連接
$link = @mysqli_connect("localhost", "root", "", "hf2s_fruitstore") or die("無法連上mySQL: " . mysqli_connect_error());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 接收來自表單的資料
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // 加密密碼

    // 準備 SQL 插入語句，使用 prepared statement 避免 SQL 注入
    $stmt = $link->prepare("INSERT INTO `register` (name, phone, address, email, password) VALUES (?, ?, ?, ?, ?)");  //? 是佔位符
    $stmt->bind_param("sssss", $name, $phone, $address, $email, $password); 
    /* s：表示對應的參數是字串 (string)。
       i：表示對應的參數是整數 (integer)。
       d：表示對應的參數是浮點數 (double)。
       b：表示對應的參數是二進制數據（如文件）。*/

    // 執行 SQL 語句
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "註冊成功！"]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }    

    // 關閉 statement
    $stmt->close();
}

// 關閉連接
$link->close();

//$timetamp = time();
//$datetime = date('Y-m-d H:i:s',$timetamp);
?>
