<?php
header('Content-Type: application/json');
session_start(); // 啟用 session

// 資料庫連線設定 (使用你的設定)
$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "animal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["state" => false, "message" => "資料庫連線失敗"]);
    exit();
}

// 解析前端傳來的 JSON 資料
$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// 查詢使用者資料
$sql = "SELECT ID, Username, Password, Uid01 FROM member WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['Password'])) {
        // 登入成功後設置 session 和 cookie
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['Username'] = $user['Username'];

        // 設置 Uid01 的 cookie (7 天有效)
        setcookie('Uid01', $user['Uid01'], time() + (86400 * 7), "/");

        echo json_encode([
            "state" => true,
            "message" => "登入成功",
            "data" => [
                "Username" => $user['Username'],
                "Uid01" => $user['Uid01'],
                "user_id" => $user['ID']
            ]
        ]);
    } else {
        echo json_encode(["state" => false, "message" => "密碼錯誤"]);
    }
} else {
    echo json_encode(["state" => false, "message" => "找不到使用者"]);
}

$conn->close();
?>
