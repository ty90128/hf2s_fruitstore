<?php
session_start();  // 啟動 session


$user_id = $_SESSION['user_id'];

// 商品清單
$products = [
    "mango" => ["愛文芒果禮盒", 2000],
    "mellon" => ["哈密瓜禮盒", 1300],
    "cherry" => ["櫻桃禮盒", 1250],
    "grape" => ["麝香葡萄禮盒", 1650],
    "comprehensive_3" => ["綜合水果禮盒1", 1100],
    "comprehensive_4" => ["綜合水果禮盒2", 1300],
    "comprehensive_2" => ["綜合水果禮盒3", 1550],
    "comprehensive" => ["綜合水果禮盒4", 1700],
    "peach" => ["水蜜桃禮盒", 1650],
    "avocado" => ["酪梨禮盒", 1300],
    "apple" => ["日本富士蘋果禮盒", 1500],
    "pear" => ["水梨禮盒", 1000],
];

// 連接資料庫
$link = @mysqli_connect('localhost', 'root', '', 'hf2s_fruitstore');
if (!$link) {
    die('資料庫連線失敗: ' . mysqli_connect_error());
}

// 處理表單提交的數據
foreach ($products as $key => [$name, $price]) {
    if (isset($_POST["add_$key"])) {
        $quantity = isset($_POST["p_no_$key"]) ? (int)$_POST["p_no_$key"] : 0;
        if ($quantity > 0) {
            $total = $price * $quantity;

            // 檢查是否已經有相同的商品在購物車
            $query = "SELECT amount, total FROM `add` WHERE user_id = ? AND pname = ?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("is", $user_id, $name);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($existing_amount, $existing_total);

            if ($stmt->num_rows > 0) {
                // 商品已存在，更新數量和總價
                $stmt->fetch();
                $new_quantity = $existing_amount + $quantity;
                $new_total = $existing_total + $total;
                $update_query = "UPDATE `add` SET amount = ?, total = ? WHERE user_id = ? AND pname = ?";
                $update_stmt = $link->prepare($update_query);
                $update_stmt->bind_param("idis", $new_quantity, $new_total, $user_id, $name);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // 商品不存在，插入新記錄
                $insert_query = "INSERT INTO `add` (user_id, pname, price, amount, total, ts, pay, state, refund, cancel) VALUES (?, ?, ?, ?, ?, ?, '待付款', '未出貨', '未退貨/款', '未取消')";
                $insert_stmt = $link->prepare($insert_query);
                $datetime = date('Y-m-d H:i:s');
                $insert_stmt->bind_param("isiidd", $user_id, $name, $price, $quantity, $total, $datetime);
                $insert_stmt->execute();
                $insert_stmt->close();
            }

            $stmt->close();
        } else {
            echo "商品 $key 數量無效！";
        }
    }
}

mysqli_close($link);

// 成功提示
echo '<script type="text/javascript">';
echo 'alert("商品已加入購物車！");';
echo 'window.history.go(-1);';
echo '</script>';
?>
