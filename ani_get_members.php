<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "animal"; // 實際資料庫名稱

try {
    // 建立資料庫連線
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL 查詢語句，從 member 資料表獲取資料
    $stmt = $conn->prepare("SELECT ID, Username, Email, Region, Created_at, role FROM member");
    $stmt->execute();

    // 取得查詢結果並轉換為關聯陣列
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 如果有資料，回傳 JSON 資料
    if (!empty($members)) {
        echo json_encode(["state" => true, "data" => $members]);
    } else {
        echo json_encode(["state" => true, "data" => [], "message" => "目前無會員資料"]);
    }
} catch (PDOException $e) {
    // 錯誤處理，回傳失敗訊息
    echo json_encode(["state" => false, "message" => "資料庫錯誤：" . $e->getMessage()]);

}


?>
