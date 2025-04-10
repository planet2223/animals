<?php

header('Content-Type: application/json');
session_start(); // 啟用 session

// 資料庫連線設定
$servername = "localhost"; // 資料庫伺服器
$username = "owner01"; // 資料庫使用者名稱
$password = "123456"; // 資料庫密碼
$dbname = "animal"; // 資料庫名稱

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("資料庫連線失敗: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["state" => false, "message" => $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

// 解析 JSON 輸入資料
$inputData = file_get_contents('php://input');
$input = json_decode($inputData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "state" => false,
        "message" => "無效的 JSON 資料: " . json_last_error_msg(),
        "received_data" => $inputData
    ]);
    exit;
}

// 檢查動作類型
try {
    switch ($action) {
        case 'checkuid':
            checkUid($conn);
            break;

        case 'checkuni':
            checkUsername($input, $conn);
            break;

        case 'register':
            registerUser($input, $conn);
            break;

        case 'login':
            loginUser($input, $conn);
            break;

        case 'logout':
            logoutUser();
            break;

        default:
            throw new Exception("無效的動作: " . $action);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["state" => false, "message" => $e->getMessage()]);
}

$conn->close();

// 檢查 UID 及登入狀態
function checkUid($conn) {
    $Uid01 = $_COOKIE['Uid01'] ?? ($_SESSION['Uid01'] ?? null);

    if ($Uid01) {
        // 查詢使用者的 Username 和 role
        $stmt = $conn->prepare("SELECT Username, role FROM member WHERE Uid01 = ?");
        $stmt->bind_param("s", $Uid01);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode([
                "state" => true,
                "data" => [
                    "Username" => $user['Username'],
                    "role" => $user['role']  // 回傳角色資訊
                ]
            ]);
        } else {
            echo json_encode(["state" => false, "message" => "Uid01 無效"]);
        }
    } else {
        echo json_encode(["state" => false, "message" => "未登入"]);
    }
}

// 檢查帳號是否重複
function checkUsername($input, $conn) {
    $username = $input['username'] ?? '';

    if (empty($username)) {
        echo json_encode(["state" => false, "message" => "缺少帳號"]);
        return;
    }

    $sql = "SELECT * FROM member WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["state" => false, "message" => "帳號已存在"]);
    } else {
        echo json_encode(["state" => true, "message" => "帳號可使用"]);
    }
}

// 註冊新使用者 (加密密碼)
function registerUser($input, $conn) {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $email = $input['email'] ?? '';
    $Uid01 = uniqid(); // 產生唯一 UID
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // 密碼加密

    if ($username && $password && $email) {
        $sql = "INSERT INTO member (Username, Password, Email, Uid01) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $Uid01);
        if ($stmt->execute()) {
            echo json_encode([
                "state" => true,
                "message" => "註冊成功",
                "data" => [
                    "Username" => $username,
                    "Uid01" => $Uid01
                ]
            ]);
        } else {
            throw new Exception("註冊失敗: " . $stmt->error);
        }
    } else {
        throw new Exception("缺少必要資訊");
    }
}

// 使用者登入 (檢查加密密碼)
function loginUser($input, $conn) {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["state" => false, "message" => "缺少帳號或密碼"]);
        return;
    }

    $sql = "SELECT Username, Password, Uid01 FROM member WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['Password'] ?? null;

        if (password_verify($password, $hashed_password)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Uid01'] = $user['Uid01'];
            setcookie('Uid01', $user['Uid01'], time() + 3600 * 24 * 7, "/");

            echo json_encode([
                "state" => true,
                "message" => "登入成功",
                "data" => [
                    "Username" => $user['Username'],
                    "Uid01" => $user['Uid01']
                ]
            ]);
        } else {
            echo json_encode(["state" => false, "message" => "密碼比對失敗"]);
        }
    } else {
        echo json_encode(["state" => false, "message" => "找不到使用者"]);
    }
}

// 使用者登出
function logoutUser() {
    session_start();

    // 清除所有 session 變數
    $_SESSION = array();

    // 終止 session
    session_unset();
    session_destroy();

    // 清除 Uid01 Cookie
    setcookie('Uid01', '', time() - 3600, '/', '', false, true); // 路徑 / 可確保正確清除

    // 回傳成功訊息
    echo json_encode(["state" => true, "message" => "登出成功"]);
}

?>
