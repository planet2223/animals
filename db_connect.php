<?php
$servername = "localhost";
$username = "owner01"; 
$password = "123456"; 
$dbname = "animal"; 

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗：" . $conn->connect_error);
}

// 設定 UTF-8 確保中文不亂碼
$conn->set_charset("utf8");
?>
