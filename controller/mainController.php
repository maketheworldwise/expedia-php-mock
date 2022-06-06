<?php

    require 'function.php';
    require 'jwt_secret_key.php';
    $res = (Object)Array();
    $result = (Object)Array();
    header('Content-Type: json');
    $req = json_decode(file_get_contents("php://input"));
    try {
        addAccessLogs($accessLogs, $req);
        switch ($handler) {
            case "index":
                echo "API Server";
                break;

            case "ACCESS_LOGS":
                //header('content-type text/html charset=utf-8');
                header('Content-Type: text/html; charset=UTF-8');

                getLogs("./logs/access.log");
                break;
            case "ERROR_LOGS":
                //header('content-type text/html charset=utf-8');
                header('Content-Type: text/html; charset=UTF-8');

                getLogs("./logs/errors.log");
                break;
            /*
            * API No. 0
            * API Name : 테스트 API
            * 마지막 수정 날짜 : 18.08.16
            */
            case "test":
                http_response_code(200);
                //$res->result = test();
                $res->date = date("Y-m-d H:i:s");
                $res->code = 100;
                $res->message = "테스트 성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            /* ************************************************************************* */
            /*
            * API No. 1
            * API Name : 관리자 API (정보 추가)
            * 마지막 수정 날짜 : 19.04.17
            */
            case "add_hotel":
                $Name = $_POST["Name"];
                $Location = $_POST["Location"];
                $Content = $_POST["Content"];
                $Ratings = $_POST["Ratings"];
                $lat = $_POST["lat"];
                $lng = $_POST["lng"];
                $Sdate = $_POST["Sdate"];
                $Edate = $_POST["Edate"];

                http_response_code(200);
                $res->result = add_hotel($Name, $Location, $Content, $Ratings, $lat, $lng, $Sdate, $Edate);
                $res->code = 100;
                $res->message = "호텔 추가 성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            
            case "add_hotel_image":
                $Hno = $_POST["Hno"];
                $URL = $_POST["URL"];

                http_response_code(200);
                $res->result = add_hotel_image($Hno, $URL);
                $res->code = 100;
                $res->message = "호텔 이미지 추가 성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            
            case "add_room":
                $Hno = $_POST["Hno"];
                $Grade = $_POST["Grade"];
                $Bed = $_POST["Bed"];
                $Price = $_POST["Price"];
                $Content = $_POST["Content"];

                http_response_code(200);
                $res->result = add_room($Hno, $Grade, $Bed, $Price, $Content);
                $res->code = 100;
                $res->message = "방 추가 성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            
            case "add_room_image":
                $Rno = $_POST["Rno"];
                $URL = $_POST["URL"];

                http_response_code(200);
                $res->result = add_room_image($Rno, $URL);
                $res->code = 100;
                $res->message = "방 이미지 추가 성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            
            case "add_discount":
                $Rno = $_POST["Rno"];
                $Percentage = $_POST["Percentage"];
                $Upload = date("Y-m-d");

                http_response_code(200);
                $res->result = add_discount($Rno, $Percentage, $Upload);
                $res->code = 100;
                $res->message = "할인된 방 추가 성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            
            /* ************************************************************************* */
            /*
            * API No. 2
            * API Name : 회원가입 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "user":
                $Email = $req->Email;
                $Pw = $req->Pw;
                $Name = $req->Name;
                
                http_response_code(200);
                
                if(!isset($Email) || !isset($Pw) || !isset($Name)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                //공백 유효성 검사
                if($Email == '' || $Pw == '' || $Name == ''){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(!preg_match("/([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/",$Email)){
                    $res->code = 501;
                    $res->message = "잘못된 이메일 형식";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(ValidEmail($Email) != NULL) {
                    $res->code = 502;
                    $res->message = "존재하는 이메일";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                $res->result = user($Email, $Pw, $Name);
                $code = 100;
                $message = "성공";  
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 3
            * API Name : 회원탈퇴 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "user_delete":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $user_email = $info->Email;
            
                http_response_code(200);
                user_delete($user_email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
            
            /* ************************************************************************* */
            /*
            * API No. 4
            * API Name : 로그인 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "login":
                $Email = $req->Email;
                $Pw = $req->Pw;
                $FCM_token = $req->FCM;

                if(!isset($Email) || !isset($Pw)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidEmail($Email) == NULL) {
                    $res->code = 503;
                    $res->message = "존재하지 않는 회원";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                $res->result = ValidLogin($Email, $Pw);
                if($res->result == NULL) {
                    $res->code = 508;
                    $res->message = "비밀번호가 맞지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                //로그인 성공시 JWT 발급 코드
                $jwt = getJWToken($Email, $Pw, JWT_SECRET_KEY);
                $res->token->jwt = $jwt;

                //FCM 토큰 저장
                $res_FCM = update_FCM($Email, $FCM_token);
                if($res_FCM && isset($FCM_token)){
                    $res->FCM_token = "FCM 토큰 저장 성공";
                }

                http_response_code(200);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /* ************************************************************************* */
            /*
            * API No. 5
            * API Name : 80,000이하 특가 호텔 목록 API
            * 마지막 수정 날짜 : 19.04.17
            */
            case "discounted_80000":

                http_response_code(200);
                $res->result = discounted_80000();
                $res->code = 100;
                $res->message = "80,000이하 특가 호텔 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 6
            * API Name : 일일 특가 호텔 목록 API
            * 마지막 수정 날짜 : 19.04.17
            */
            case "discounted_today":
                $Today = (string)date("Y-m-d");
                
                http_response_code(200);
                $res->result = discounted_today($Today);
                $res->code = 100;
                $res->message = "일일 특가 호텔 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 7
            * API Name : 마감 특가 호텔 목록 API
            * 마지막 수정 날짜 : 19.04.17
            */
            case "discounted_fin":
                $Today = (string)date("Y-m-d");
                
                http_response_code(200);
                $res->result = discounted_fin($Today);
                $res->code = 100;
                $res->message = "마감 특가 호텔 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 8
            * API Name : 특가 호텔 방 상세정보 API
            * 마지막 수정 날짜 : 19.04.17
            */
            case "discounted_more":
                $Hno = $_GET["Hno"];

                http_response_code(200);
                $res->result = discounted_more($Hno);
                $res->code = 100;
                $res->message = "특가 호텔 방 상세정보";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /* ************************************************************************* */
            /*
            * API No. 9
            * API Name : 전체 호텔 목록 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "hotel":
                http_response_code(200);
                $res->result = hotel();
                $res->code = 100;
                $res->message = "전체 호텔 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 10
            * API Name : 전체 방 목록 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "room":                
                $Hno = $_GET["Hno"];

                http_response_code(200);
                $res->result = room($Hno);
                $res->code = 100;
                $res->message = "전체 방 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 11
            * API Name : 호텔 이미지 목록 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "hotel_image":                
                $Hno = $_GET["Hno"];

                if(ValidHotel($Hno)) {
                    $res->code = 511;
                    $res->message = "없는 호텔입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                http_response_code(200);
                $res->result = hotel_image($Hno);
                $res->code = 100;
                $res->message = "전체 방 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 12
            * API Name : 방 이미지 목록 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "room_image":                
                $Hno = $_GET["Hno"];
                $Rno = $_GET["Rno"];

                if(ValidHotel($Hno)) {
                    $res->code = 511;
                    $res->message = "없는 호텔입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidRoom($Hno, $Rno)) {
                    $res->code = 512;
                    $res->message = "없는 방입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                http_response_code(200);
                $res->result = room_image($Hno, $Rno);
                $res->code = 100;
                $res->message = "전체 방 목록";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
                 
            /* ************************************************************************* */
            /*
            * API No. 13
            * API Name : 호텔 필터링 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "hotel_filter":                
                $Name = $_GET["Name"];
                $Sdate = $_GET["Sdate"];
                $People = $_GET["People"];

                switch($People) {
                    case '1': $Bed = "Single"; break;
                    case '2': $Bed = "Double"; break;
                    case '3': $Bed = "Triple"; break;
                    case '4': $Bed = "Classic"; break;
                    default: $Bed = "Grand"; break;
                }
               
                http_response_code(200);
                $res->result = hotel_filter($Name, $Sdate, $Bed);
                $res->code = 100;   
                $res->message = "호텔 필터링";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /* ************************************************************************* */
            /*
            * API No. 14
            * API Name : 예약 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "book":                
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                $Rno = $req->Rno;
                $FName = $req->FName;
                $LName = $req->LName;
                $Sdate = $req->Sdate;
                $Edate = $req->Edate;

                if (!Validdate($Sdate, $Edate)) {
                    $res->code = 509;
                    $res->message = "해당 날짜에 해당하는 특가 호텔이 없습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if (!Validbook($Rno)) {
                    $res->code = 510;
                    $res->message = "이미 예약이 되어 있습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = book($Rno, $FName, $LName, $Email, $Sdate, $Edate);
                $res->code = 100;   
                $res->message = "예약 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 15
            * API Name : 예약 확인 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "book_check":                
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = book_check($Email);
                $res->code = 100;   
                $res->message = "예약 확인";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 16
            * API Name : 예약 취소 API
            * 마지막 수정 날짜 : 19.04.06
            */
            case "book_cancel":                
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                $Rno = $req->Rno;

                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = book_cancel($Rno, $Email);
                $res->code = 100;   
                $res->message = "예약 취소";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /* ************************************************************************* */
            /*
            * API No. 17
            * API Name : FCM API
            * 마지막 수정 날짜 : 19.04.07
            */
            case "token":                
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                $res->code = 100;   
                $res->message = "유효한 토큰 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;
            
            case "sendfcm_new":                
                $res->result = sendfcm_new();
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;


        }
    } catch (Exception $e) {

        return getSQLErrorException($errorLogs, $e, $req);
    }