<?php
    // {"username" : "owner01", "password" : "123456", "email" : "owner01@test.com"}
    // {"state" : true, "message" : "註冊成功"}
    // {"state" : false, "message" : "新增失敗與相關錯誤訊息"}
    // {"state" : false, "message" : "欄位錯誤"}
    // {"state" : false, "message" : "欄位不得為空"}
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $data = file_get_contents("php://input");
        $input = json_decode($data, true);
        if(isset($input["username"], $input["password"], $input["email"])){
            $p_username = trim($input["username"]);
            $p_password = password_hash(trim($input["password"]), PASSWORD_DEFAULT);
            $p_email    = trim($input["email"]);
            if($p_username && $p_password && $p_email){
                $conn = mysqli_connect('localhost', 'owner01', '123456', 'animal');
                if(!$conn){
                    echo json_encode(["state" => false, "message" => "連線失敗!"]);
                    exit;
                }
            
                $stmt = $conn->prepare("INSERT INTO member (Username, Password, Email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $p_username, $p_password, $p_email); //一定要傳遞變數

                if($stmt->execute()){
                    echo json_encode(["state" => true, "message" => "註冊成功!"]);
                }else{
                    echo json_encode(["state" => false, "message" => "註冊失敗!"]);
                }
                $stmt->close();
                $conn->close();
            }else{
                echo json_encode(["state" => false, "message" => "欄位不得為空!"]);
            }
        }else{
            echo json_encode(["state" => false, "message" => "欄位錯誤!"]);
        }
    }else{
        echo json_encode(["state" => false, "message" => "無效的請求方法!"]);
    }
?>