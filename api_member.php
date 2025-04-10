<?php
session_start(); // 確保 Session 正確啟動
header('Content-Type: application/json; charset=UTF-8');

// 檢查 Session 目錄是否可寫
$session_path = ini_get('session.save_path');
if (!is_writable($session_path)) {
    error_log("[錯誤] Session 目錄無法寫入: $session_path");
}

// 確保 Session 設定
ini_set('session.cookie_secure', 0); // 若無 HTTPS，請設為 0
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

require 'db_connect.php'; // 連接資料庫

// Debug 訊息
error_log("API called: " . json_encode($_GET)); // 記錄 API 呼叫
error_log("Session Status: " . session_status());
error_log("Session Data: " . json_encode($_SESSION));
error_log("Cookies: " . json_encode($_COOKIE));

// 嘗試從 Cookie 恢復 Session
if (!isset($_SESSION['user_id']) && isset($_COOKIE['Uid01'])) {
    $uid = $_COOKIE['Uid01'];
    $stmt = $conn->prepare("SELECT id, role FROM member WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
    }
}

// 確保用戶已登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["state" => false, "message" => "未登入"]);
    exit;
}

// 限制只有 admin 才能存取
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["state" => false, "message" => "無權限操作"]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == "list") {
    // 取得會員列表
    $result = $conn->query("SELECT id, username, role FROM member");
    if ($result) {
        $members = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["state" => true, "data" => $members ?: []]);
    } else {
        echo json_encode(["state" => false, "message" => "查詢會員列表失敗"]);
    }
} elseif ($action == "update") {
    // 更新會員角色
    $id = intval($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? '';
    if ($id <= 0 || empty($role)) {
        echo json_encode(["state" => false, "message" => "缺少必要參數"]);
        exit;
    }
    $stmt = $conn->prepare("UPDATE member SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    echo json_encode(["state" => $stmt->execute(), "message" => $stmt->execute() ? "會員更新成功" : "更新失敗"]);
} elseif ($action == "delete") {
    // 刪除會員
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(["state" => false, "message" => "缺少必要參數"]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM member WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo json_encode(["state" => $stmt->execute(), "message" => $stmt->execute() ? "會員已刪除" : "刪除失敗"]);
} else {
    echo json_encode(["state" => false, "message" => "無效操作"]);
}
?>
