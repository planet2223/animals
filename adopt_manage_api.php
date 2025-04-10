<?php

require_once "db_connect.php";  // ✅ 確保已載入資料庫連線

// ✅ 啟動 session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ 顯示錯誤訊息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ 資料庫連線設定
$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "animal";

// ✅ 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// ✅ 檢查連線
if ($conn->connect_error) {
    die(json_encode(["state" => false, "message" => "資料庫連線失敗：" . $conn->connect_error]));
}

header('Content-Type: application/json');

// ✅ 取得動作參數
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addAnimal($conn);
        break;
    case 'list':
        listAnimals($conn);
        break;
    case 'get':  
        getAnimal($conn);
        break;
    case 'update':
        updateAnimal($conn);
        break;
    case 'delete':
        deleteAnimal($conn);
        break;
    case 'list_all':  // ✅ 新增 `list_all`
        listAllAnimals($conn);
        break;
    default:
        echo json_encode(["state" => false, "message" => "無效的操作：" . htmlspecialchars($action)]);
        break;
}

// ✅ 新增動物資料
function addAnimal($conn) {
    $Uid01 = $_POST['Uid01'] ?? null;
    $pet_name = trim($_POST['pet_name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $age = $_POST['age'] ?? null;
    $gender = trim($_POST['gender'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = 'imgs/ani19.jpg'; // 預設圖片

    // ✅ 圖片上傳邏輯
    if (!empty($_FILES['animal_image']['name'])) {
        $target_dir = "imgs/";
        $file_name = time() . "_" . basename($_FILES["animal_image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (!is_dir($target_dir) || !is_writable($target_dir)) {
            echo json_encode(["state" => false, "message" => "資料夾權限錯誤：$target_dir 無法寫入"]);
            return;
        }

        if ($_FILES['animal_image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["state" => false, "message" => "圖片上傳錯誤，錯誤碼：" . $_FILES['animal_image']['error']]);
            return;
        }

        if (move_uploaded_file($_FILES["animal_image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        } else {
            echo json_encode(["state" => false, "message" => "圖片搬移失敗"]);
            return;
        }
    }

    // ✅ 透過 Uid01 找 user_id
    $stmt = $conn->prepare("SELECT ID FROM member WHERE Uid01 = ?");
    $stmt->bind_param("s", $Uid01);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(["state" => false, "message" => "找不到會員"]);
        return;
    }
    $user = $result->fetch_assoc();
    $user_id = $user['ID'];
    $stmt->close();

    // ✅ 新增動物資料
    $stmt = $conn->prepare("
        INSERT INTO adopt_animals (user_id, pet_name, species, age, gender, location, description, image_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ississss", $user_id, $pet_name, $species, $age, $gender, $location, $description, $image_url);

    if ($stmt->execute()) {
        echo json_encode(["state" => true, "message" => "動物資料已成功新增"]);
    } else {
        echo json_encode(["state" => false, "message" => "新增失敗：" . $stmt->error]);
    }

    $stmt->close();
}

// ✅ 讀取使用者的動物資料
function listAnimals($conn) {
    // ✅ 讀取前端傳遞的 JSON 資料
    $inputJSON = file_get_contents("php://input");
    $input = json_decode($inputJSON, true);
    $Uid01 = $input['Uid01'] ?? null;

    if (!$Uid01) {
        echo json_encode(["state" => false, "message" => "請先登入"]);
        return;
    }

    // ✅ 透過 Uid01 找 user_id
    $stmt = $conn->prepare("SELECT ID FROM member WHERE Uid01 = ?");
    $stmt->bind_param("s", $Uid01);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["state" => false, "message" => "找不到會員"]);
        return;
    }

    $user = $result->fetch_assoc();
    $user_id = $user['ID'];
    $stmt->close();

    // ✅ 只撈取該使用者上傳的動物
    $stmt = $conn->prepare("SELECT * FROM adopt_animals WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $animals = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = !empty($row['image_url']) ? "/web202411/" . $row['image_url'] : "/web202411/imgs/ani19.jpg";
        $animals[] = $row;
    }

    echo json_encode(["state" => true, "data" => $animals]);
}

// ✅ 讀取單個動物資料 (get)
function getAnimal($conn) {
    $inputData = json_decode(file_get_contents("php://input"), true);
    $id = $inputData['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(["state" => false, "message" => "缺少有效的動物 ID"]);
        return;
    }

    $stmt = $conn->prepare("SELECT * FROM adopt_animals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $animal = $result->fetch_assoc();
    $stmt->close();

    if ($animal) {
        echo json_encode(["state" => true, "data" => $animal]);
    } else {
        echo json_encode(["state" => false, "message" => "找不到該動物"]);
    }
}

// ✅ 更新動物資料 (update)
function updateAnimal($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["id"], $data["pet_name"], $data["species"], $data["age"], $data["gender"], $data["location"])) {
        echo json_encode(["state" => false, "message" => "請完整填寫所有欄位"]);
        return;
    }

    $stmt = $conn->prepare("
        UPDATE adopt_animals 
        SET pet_name=?, species=?, age=?, gender=?, location=?, description=? 
        WHERE id=?
    ");
    $stmt->bind_param("ssisssi", 
        $data["pet_name"], 
        $data["species"], 
        $data["age"], 
        $data["gender"], 
        $data["location"], 
        $data["description"], 
        $data["id"]
    );

    if ($stmt->execute()) {
        echo json_encode(["state" => true, "message" => "動物資料已成功更新"]);
    } else {
        echo json_encode(["state" => false, "message" => "更新失敗：" . $stmt->error]);
    }

    $stmt->close();
}


// ✅ 刪除動物的 PHP 函數
function deleteAnimal($conn) {
    // 確保正確解析 JSON 請求
    $inputData = json_decode(file_get_contents("php://input"), true);
    $id = $inputData['id'] ?? $_POST['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(["state" => false, "message" => "缺少有效的 ID"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM adopt_animals WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["state" => true, "message" => "動物資料已成功刪除"]);
    } else {
        echo json_encode(["state" => false, "message" => "刪除失敗：" . $stmt->error]);
    }

    $stmt->close();
}

// ✅ 修正 `listAllAnimals()` 確保正確回傳 JSON
function listAllAnimals($conn) {
    header("Content-Type: application/json; charset=UTF-8");

    $sql = "SELECT * FROM adopt_animals ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $animals = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $animals[] = [
                "id" => (int)$row["id"],
                "pet_name" => $row["pet_name"] ?? "未命名",
                "species" => $row["species"] ?? "未知",
                "age" => $row["age"] ?? "未知",
                "gender" => $row["gender"] ?? "未知",
                "location" => $row["location"] ?? "未知",
                "description" => $row["description"] ?? "無詳細資訊",
                "image_url" => !empty($row["image_url"]) ? "/web202411/" . $row["image_url"] : "/web202411/imgs/placeholder.jpg"
            ];
        }
    }

    // ✅ 確保回傳 JSON
    echo json_encode(["state" => true, "data" => $animals], JSON_UNESCAPED_UNICODE);
    exit;
}

// ✅ 確保連線只關閉一次
if ($conn && $conn->ping()) {
    $conn->close();
}
?>
