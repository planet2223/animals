<?php
//連線animal資料庫及上傳資料

$servername = "localhost";
$username = "owner01"; // phpMyAdmin 預設使用者
$password = "123456"; // phpMyAdmin 預設密碼（如果有設定請修改）
$dbname = "animal";

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 讀取 JSON 檔案
$jsonData = file_get_contents("js/data.json");
$animals = json_decode($jsonData, true);

function formatDate($date) {
    if (empty($date)) {
        return "NULL"; // 如果日期是空的，存 NULL，防止 MySQL 報錯
    }
    return "'" . str_replace("/", "-", $date) . "'"; // 替換 "/" 為 "-"，符合 MySQL 格式
}

foreach ($animals as $animal) {
    $animal_opendate = formatDate($animal['animal_opendate']);
    $animal_update = formatDate($animal['animal_update']);
    $animal_createtime = formatDate($animal['animal_createtime']);

    $sql = "INSERT INTO animals (animal_id, animal_subid, animal_area_pkid, animal_shelter_pkid, animal_place, 
            animal_kind, animal_variety, animal_sex, animal_bodytype, animal_colour, animal_age, animal_sterilization, 
            animal_bacterin, animal_foundplace, animal_status, animal_opendate, animal_closeddate, 
            animal_update, animal_createtime, shelter_name, album_file, shelter_address, shelter_tel) 
            VALUES ('{$animal['animal_id']}', '{$animal['animal_subid']}', '{$animal['animal_area_pkid']}', 
            '{$animal['animal_shelter_pkid']}', '{$animal['animal_place']}', '{$animal['animal_kind']}', 
            '{$animal['animal_Variety']}', '{$animal['animal_sex']}', '{$animal['animal_bodytype']}', 
            '{$animal['animal_colour']}', '{$animal['animal_age']}', '{$animal['animal_sterilization']}', 
            '{$animal['animal_bacterin']}', '{$animal['animal_foundplace']}', '{$animal['animal_status']}', 
            $animal_opendate, '{$animal['animal_closeddate']}', $animal_update, $animal_createtime, 
            '{$animal['shelter_name']}', '{$animal['album_file']}', '{$animal['shelter_address']}', '{$animal['shelter_tel']}');";

    echo "<pre>$sql</pre>";  // 顯示 SQL 語法，確認沒錯誤
}

?>
