<?php
$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "animal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

$sql = "SELECT * FROM animals WHERE animal_status='OPEN'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>領養平台</title>
    <style>
        .animal { border: 1px solid #ccc; padding: 10px; margin: 10px; display: inline-block; width: 250px; }
        .animal img { width: 100%; height: auto; }
    </style>
</head>
<body>
    <h1>可認養動物列表</h1>
    <div>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='animal'>";
                echo "<img src='{$row['album_file']}' alt='動物圖片'>";
                echo "<p>品種: {$row['animal_variety']}</p>";
                echo "<p>性別: {$row['animal_sex']}</p>";
                echo "<p>年齡: {$row['animal_age']}</p>";
                echo "<p>收容所: {$row['shelter_name']}</p>";
                echo "<p>地址: {$row['shelter_address']}</p>";
                echo "<p>聯絡電話: {$row['shelter_tel']}</p>";
                echo "</div>";
            }
        } else {
            echo "<p>目前沒有可認養動物</p>";
        }
        ?>
    </div>
</body>
</html>
