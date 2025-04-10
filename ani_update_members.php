<?php
header('Content-Type: application/json');

// 取得 POST 傳來的會員資料
$memberId = $_POST['ID'];
$username = $_POST['Username'];
$email = $_POST['Email'];
$region = $_POST['Region'];
$role = $_POST['Role'];

// 資料庫連線設定
$servername = "localhost";
$dbusername = "owner01";
$password = "123456";
$dbname = "animal";

try {
    // 建立資料庫連線
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL 語句：更新會員資料
    $stmt = $conn->prepare("UPDATE member SET Username = ?, Email = ?, Region = ?, role = ? WHERE ID = ?");
    $stmt->execute([$username, $email, $region, $role, $memberId]);

    // 如果更新成功，回傳 JSON 訊息
    if ($stmt->rowCount() > 0) {
        echo json_encode(["state" => true, "message" => "會員資料更新成功"]);
    } else {
        echo json_encode(["state" => false, "message" => "無法找到對應的會員 ID 或資料未更改"]);
    }
} catch (PDOException $e) {
    echo json_encode(["state" => false, "message" => "資料庫錯誤：" . $e->getMessage()]);
}
?>
