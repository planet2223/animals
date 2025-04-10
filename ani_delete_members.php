<?php
header('Content-Type: application/json');

// 取得 POST 的會員 ID
$memberId = $_POST['ID'];

// 資料庫連線設定
$servername = "localhost";
$dbusername = "owner01";
$password = "123456";
$dbname = "animal";

try {
    // 建立資料庫連線
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 刪除會員的 SQL 語句
    $stmt = $conn->prepare("DELETE FROM member WHERE ID = ?");
    $stmt->execute([$memberId]);

    // 檢查是否成功刪除
    if ($stmt->rowCount() > 0) {
        echo json_encode(["state" => true, "message" => "會員已成功刪除"]);
    } else {
        echo json_encode(["state" => false, "message" => "找不到該會員 ID"]);
    }
} catch (PDOException $e) {
    echo json_encode(["state" => false, "message" => "資料庫錯誤：" . $e->getMessage()]);
}
?>
