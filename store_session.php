<?php
session_start();  // 重要：開啟 Session

// 取得前端送來的 JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['user_id'])) {
    // 如果有帶 user_id，就寫進 Session
    $_SESSION['user_id'] = $data['user_id'];

    // 回傳給前端 (success: true)
    echo json_encode([
        'success' => true,
        'message' => 'Session user_id 已成功寫入'
    ]);
} else {
    // 沒有帶 user_id
    echo json_encode([
        'success' => false,
        'message' => '未帶 user_id'
    ]);
}
// 例：debug 模式手動加
error_log("Your session_id: " . session_id());

?>
