<?php
session_start();

// 清除 session 中的 user_id 和 guest_id
unset($_SESSION['user_id']);
unset($_SESSION['guest_id']);

// 完成登出
echo "登出成功";
?>
