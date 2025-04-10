<?php
    // {"uid01" : "owner01"}
    // {"state" : true, "message" : "驗證成功", "data" : "使用者資訊"}
    // {"state" : false, "message" : "驗證失敗與相關錯誤訊息"}
    // {"state" : false, "message" : "欄位錯誤"}
    // {"state" : false, "message" : "欄位不得為空"}
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $data = file_get_contents("php://input");
        $input = json_decode($data, true);
        if(isset($input["uid01"])){
            $p_uid = trim($input["uid01"]);
            if($p_uid){
                $conn = mysqli_connect('localhost', 'owner01', '123456', 'animal');
                if(!$conn){
                    echo json_encode(["state" => false, "message" => "連線失敗!"]);
                    exit;
                }
            
                $stmt = $conn->prepare("SELECT Username, Email, Uid01, Created_at FROM member WHERE Uid01 = ?");
                $stmt->bind_param("s", $p_uid); //一定要傳遞變數
                $stmt->execute();
                $result = $stmt->get_result();
               
                if($result->num_rows === 1){
                    //驗證成功
                    $userdata = $result->fetch_assoc();
                    echo json_encode(["state" => true, "message" => "驗證成功", "data" => $userdata]);
                }else{
                    echo json_encode(["state" => false, "message" => "驗證失敗"]);
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