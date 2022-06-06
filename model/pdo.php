<?php

    require "database.php";
    require "jwt_secret_key.php";

    function test(){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM TEST_TB;";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    
    /* ************************************************************************* */

    //관리자 API
    function add_hotel($Name, $Location, $Content, $Ratings, $lat, $lng, $Sdate, $Edate){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO hotel_TB(Name, Location, Content, Ratings, lat, lng, Sdate, Edate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Name, $Location, $Content, $Ratings, $lat, $lng, $Sdate, $Edate]);

        $st=null;$pdo = null;

        return true;

    }

    function add_hotel_image($Hno, $URL){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO imageH_TB(Hno, URL) VALUES (?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Hno, $URL]);

        $st=null;$pdo = null;

        return true;

    }
    
    function add_room($Hno, $Grade, $Bed, $Price, $Content){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO room_TB(Hno, Grade, Bed, Price, Content)
            VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Hno, $Grade, $Bed, $Price, $Content]);

        $st=null;$pdo = null;

        return true;

    }

    function add_room_image($Rno, $URL){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO imageR_TB(Rno, URL) VALUES (?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Rno, $URL]);

        $st=null;$pdo = null;

        return true;

    }

    function add_discount($Rno, $Percentage, $Upload){
        $pdo = pdoSqlConnect();
        $query = "SELECT Price FROM room_TB WHERE Rno = ?";
        $st = $pdo->prepare($query);
        $st->execute([$Rno]);

        //1일 숙박료
        $result = $st->fetch(); 
        $result = (int)str_replace(',', '', $result["Price"]);

        //할인 적용 금액
        $Priced = (int)$result - ((int)$result * (int)$Percentage)/100;

        //숫자 콤마
        $Priced = number_format((int)$Priced);

        $sql = "INSERT INTO discount_TB(Rno, Percentage, Priced, Upload) 
            VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$Rno, $Percentage, $Priced, $Upload]);

        $stmt=null;$st=null;$pdo = null;
        
        return true;

    }

    /* ************************************************************************* */
    
    //이메일 유효성 검사
    function ValidEmail($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM user_TB WHERE Email = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;

    }

    //로그인 유효성 검사
    function ValidLogin($Email, $Pw){
        $pdo = pdoSqlConnect();
        $query = "SELECT Email, Name FROM user_TB WHERE Email = ? AND Pw = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, $Pw, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;

    }

    //FCM 토큰 저장
    function update_FCM($Email, $FCM_token){
        $pdo = pdoSqlConnect();
        $query = "UPDATE user_TB SET Token = ? 
            WHERE Email = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$FCM_token, $Email, 'N']);

        $st=null;$pdo = null;

        return true;
    }

    /* ************************************************************************* */

    //회원가입
    function user($Email, $Pw, $Name){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO user_TB(Email, Pw, Name) VALUES (?, ?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Email, $Pw, $Name]);

        /*************************************************** */
        $sql = "SELECT * FROM user_TB WHERE Email = ? AND Deleted = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$Email, 'N']);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $res = $stmt->fetchAll();

        $stmt=null;$st=null;$pdo = null;

        return $res;
    }

    //회원탈퇴
    function user_delete($Email){
        $pdo = pdoSqlConnect();
        $query = "UPDATE user_TB SET Deleted = ? WHERE Email = ?";

        $st = $pdo->prepare($query);
        $st->execute(['Y', $Email]);

        $st=null;$pdo = null;

        return true;
    }

    /* ************************************************************************* */

    //대표 호텔 이미지 출력
    function image_add($Hno) {
        $pdo = pdoSqlConnect();

        $query = "SELECT URL FROM imageH_TB WHERE Hno = ?";
        $st = $pdo->prepare($query);
        $st->execute([$Hno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $row = $st->fetch();

        foreach($row as $value) {
            $row = $value;
        }
        
        return $row;
    }

    //대표 방 이미지 출력
    function image_add_R($Rno) {
        $pdo = pdoSqlConnect();

        $query = "SELECT URL FROM imageR_TB AS ir INNER JOIN room_TB AS r ON ir.Rno = r.Rno WHERE r.Rno = ?";
        $st = $pdo->prepare($query);
        $st->execute([$Rno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $row = $st->fetch();

        foreach($row as $value) {
            $row = $value;
        }
        
        return $row;
    }

    //80,000이하 특가 호텔 목록
    function discounted_80000(){
        $pdo = pdoSqlConnect();

        $query = "SELECT DISTINCT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, D.Percentage, D.Priced, R.Price
            FROM discount_TB AS D
            INNER JOIN room_TB AS R ON R.Rno = D.Rno
            INNER JOIN hotel_TB AS H ON H.Hno = R.Hno
            WHERE CAST(REPLACE(D.Priced, ',' ,'') AS UNSIGNED) BETWEEN ? AND ?
            AND D.Percentage = (SELECT MAX(d.Percentage) 
                FROM discount_TB AS d
                INNER JOIN room_TB AS r ON r.Rno = d.Rno
                INNER JOIN hotel_TB AS h ON h.Hno = r.Hno
                WHERE h.Hno = H.Hno
                AND r.Booked = ? AND d.Booked = ? AND d.Deleted = ?) 
            AND CAST(REPLACE(D.Priced, ',' ,'') AS UNSIGNED) = (SELECT MIN(CAST(REPLACE(dd.Priced, ',' ,'') AS UNSIGNED))
                FROM discount_TB AS dd
                INNER JOIN room_TB AS rr ON rr.Rno = dd.Rno
                INNER JOIN hotel_TB AS hh ON hh.Hno = rr.Hno
                WHERE hh.Hno = H.Hno
                AND rr.Booked = ? AND dd.Booked = ? AND dd.Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute([0, 80000, 'N', 'N', 'N', 'N', 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Hno = $row["Hno"];
            $elements->Name = $row["Name"];
            $elements->Location = $row["Location"];
            $elements->lat = $row["lat"];
            $elements->lng = $row["lng"];
            $elements->Sdate = $row["Sdate"];
            $elements->Edate = $row["Edate"];
            $elements->Percentage = $row["Percentage"];
            $elements->Price = $row["Price"];
            $elements->discounted_Price = $row["Priced"];
            $elements->Image = image_add($row["Hno"]);
            array_push($res, $elements);
        }

        $elements=null;$st=null;$pdo = null;

        return $res;
    }

     //일일 특가 호텔 목록
     function discounted_today($Today){
        $pdo = pdoSqlConnect();

        $query = "SELECT DISTINCT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, D.Percentage, D.Priced, R.Price
            FROM discount_TB AS D
            INNER JOIN room_TB AS R ON R.Rno = D.Rno
            INNER JOIN hotel_TB AS H ON H.Hno = R.Hno
            WHERE D.Upload = ?
            AND D.Percentage >= ?
            AND D.Percentage = (SELECT MAX(d.Percentage) 
                FROM discount_TB AS d
                INNER JOIN room_TB AS r ON r.Rno = d.Rno
                INNER JOIN hotel_TB AS h ON h.Hno = r.Hno
                WHERE h.Hno = H.Hno
                AND r.Booked = ? AND d.Booked = ? AND d.Deleted = ?) 
            AND CAST(REPLACE(D.Priced, ',' ,'') AS UNSIGNED) = (SELECT MIN(CAST(REPLACE(dd.Priced, ',' ,'') AS UNSIGNED))
                FROM discount_TB AS dd
                INNER JOIN room_TB AS rr ON rr.Rno = dd.Rno
                INNER JOIN hotel_TB AS hh ON hh.Hno = rr.Hno
                WHERE hh.Hno = H.Hno
                AND rr.Booked = ? AND dd.Booked = ? AND dd.Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute(['2019-04-17', '40', 'N', 'N', 'N', 'N', 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Hno = $row["Hno"];
            $elements->Name = $row["Name"];
            $elements->Location = $row["Location"];
            $elements->lat = $row["lat"];
            $elements->lng = $row["lng"];
            $elements->Sdate = $row["Sdate"];
            $elements->Edate = $row["Edate"];
            $elements->Percentage = $row["Percentage"];
            $elements->Price = $row["Price"];
            $elements->discounted_Price = $row["Priced"];
            $elements->Image = image_add($row["Hno"]);
            array_push($res, $elements);
        }

        $elements=null;$st=null;$pdo = null;

        return $res;

    }

    //마감 특가 호텔 목록
    function discounted_fin($Today){
        $pdo = pdoSqlConnect();

        $query = "SELECT DISTINCT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, D.Percentage, D.Priced, R.Price
            FROM discount_TB AS D
            INNER JOIN room_TB AS R ON R.Rno = D.Rno
            INNER JOIN hotel_TB AS H ON H.Hno = R.Hno
            WHERE H.Sdate BETWEEN ? AND ?
            AND D.Percentage = (SELECT MAX(d.Percentage) 
                FROM discount_TB AS d
                INNER JOIN room_TB AS r ON r.Rno = d.Rno
                INNER JOIN hotel_TB AS h ON h.Hno = r.Hno
                WHERE h.Hno = H.Hno
                AND r.Booked = ? AND d.Booked = ? AND d.Deleted = ?) 
            AND CAST(REPLACE(D.Priced, ',' ,'') AS UNSIGNED) = (SELECT MIN(CAST(REPLACE(dd.Priced, ',' ,'') AS UNSIGNED))
                FROM discount_TB AS dd
                INNER JOIN room_TB AS rr ON rr.Rno = dd.Rno
                INNER JOIN hotel_TB AS hh ON hh.Hno = rr.Hno
                WHERE hh.Hno = H.Hno
                AND rr.Booked = ? AND dd.Booked = ? AND dd.Deleted = ?)";

        $st = $pdo->prepare($query); //'2019-04-21', '2019-04-28'
        $st->execute(['2019-04-21', '2019-04-28', 'N', 'N', 'N', 'N', 'N', 'N']);
        //$st->execute([$Today, (string)date("Y-m-d", strtotime($Today.'+7 days')), 'N', 'N', 'N', 'N', 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Hno = $row["Hno"];
            $elements->Name = $row["Name"];
            $elements->Location = $row["Location"];
            $elements->lat = $row["lat"];
            $elements->lng = $row["lng"];
            $elements->Sdate = $row["Sdate"];
            $elements->Edate = $row["Edate"];
            $elements->Percentage = $row["Percentage"];
            $elements->Price = $row["Price"];
            $elements->discounted_Price = $row["Priced"];
            $elements->Image = image_add($row["Hno"]);

            array_push($res, $elements);
        }

        $elements=null;$st=null;$pdo = null;

        return $res;
    }

    //특가 호텔 방 목록
    function discounted_more($Hno){
        $pdo = pdoSqlConnect();

        $query = "SELECT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, D.Percentage, D.Priced, R.Price, R.Rno, R.Grade, R.Bed
            FROM discount_TB AS D 
            INNER JOIN room_TB AS R ON R.Rno = D.Rno
            INNER JOIN hotel_TB AS H ON H.Hno = R.Hno
            WHERE D.Booked = ? AND D.Deleted = ? AND H.Hno = ?";

        $st = $pdo->prepare($query);
        $st->execute(['N', 'N', $Hno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        $resH = Array();
        while($row = $st->fetch()) {
            $elements = (object)Array();
            $hotel = (object)Array();

            $elements->Rno = $row["Rno"];
            $elements->Percentage = $row["Percentage"];
            $elements->Grade = $row["Grade"];
            $elements->Bed = $row["Bed"];
            $elements->Price = $row["Price"];
            $elements->discounted_Price = $row["Priced"];

            //해당 방에 대한 이미지 출력
            $elements->image = image_add_R($row["Rno"]);
            
            $hotel->Hno = $row["Hno"];
            $hotel->Name = $row["Name"];
            $hotel->Location = $row["Location"];
            $hotel->lat = $row["lat"];
            $hotel->lng = $row["lng"];
            $hotel->Sdate = $row["Sdate"];
            $hotel->Edate = $row["Edate"];
            $hotel->Image = image_add($Hno);

            array_push($res, $elements); 
        }
        array_push($res, $hotel); 

        $elements=null;$st=null;$pdo = null;

        return $res;

    }

    /* ************************************************************************* */

    //전체 호텔 목록
    function hotel(){
        $pdo = pdoSqlConnect();

        $query = "SELECT DISTINCT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, D.Percentage, D.Priced, R.Price
            FROM discount_TB AS D
            INNER JOIN room_TB AS R ON R.Rno = D.Rno
            INNER JOIN hotel_TB AS H ON H.Hno = R.Hno
            WHERE D.Percentage = (SELECT MAX(d.Percentage) 
                FROM discount_TB AS d
                INNER JOIN room_TB AS r ON r.Rno = d.Rno
                INNER JOIN hotel_TB AS h ON h.Hno = r.Hno
                WHERE h.Hno = H.Hno
                AND r.Booked = ? AND d.Booked = ? AND d.Deleted = ?) 
            AND CAST(REPLACE(D.Priced, ',' ,'') AS UNSIGNED) = (SELECT MIN(CAST(REPLACE(dd.Priced, ',' ,'') AS UNSIGNED))
                FROM discount_TB AS dd
                INNER JOIN room_TB AS rr ON rr.Rno = dd.Rno
                INNER JOIN hotel_TB AS hh ON hh.Hno = rr.Hno
                WHERE hh.Hno = H.Hno
                AND rr.Booked = ? AND dd.Booked = ? AND dd.Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute(['N', 'N', 'N', 'N', 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Hno = $row["Hno"];
            $elements->Name = $row["Name"];
            $elements->Location = $row["Location"];
            $elements->lat = $row["lat"];
            $elements->lng = $row["lng"];
            $elements->Sdate = $row["Sdate"];
            $elements->Edate = $row["Edate"];
            $elements->Percentage = $row["Percentage"];
            $elements->Price = $row["Price"];
            $elements->discounted_Price = $row["Priced"];
            $elements->Image = image_add($row["Hno"]);

            array_push($res, $elements);
        }

        $elements=null;$st=null;$pdo = null;

        return $res;
    }

    //전체 방 목록
    function room($Hno){
        $pdo = pdoSqlConnect();
        $query = "SELECT DISTINCT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, R.Price, R.Rno, R.Grade, R.Bed
            FROM hotel_TB AS H
            INNER JOIN room_TB AS R ON R.Hno = H.Hno 
            WHERE H.Hno = ? AND R.Booked = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Hno, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row= $st->fetch()) {
            $elements = (object)Array();
            $hotel = (object)Array();
            $elements->Rno = $row["Rno"];
            $elements->Grade = $row["Grade"];
            $elements->Bed = $row["Bed"];
            $elements->Price = $row["Price"];
            $elements->Image = image_add_R($row["Rno"]);

            $hotel->Name = $row["Name"];
            $hotel->Location = $row["Location"];
            $hotel->lat = $row["lat"];
            $hotel->lng = $row["lng"];
            $hotel->Sdate = $row["Sdate"];
            $hotel->Edate = $row["Edate"];
            $hotel->Image = image_add($row["Hno"]);

            array_push($res, $elements);
        }
        array_push($res, $hotel);

        $hotel=null;$elements=null;$st=null;$pdo = null;

        return $res;

    }

    //호텔 이미지 목록
    function hotel_image($Hno){
        $pdo = pdoSqlConnect();
        $query = "SELECT URL FROM imageH_TB WHERE Hno = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Hno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row= $st->fetch()) {
            array_push($res, $row["URL"]);
        }
        $st=null;$pdo = null;

        return $res;
    }

    //방 이미지 목록
    function room_image($Hno, $Rno){
        $pdo = pdoSqlConnect();
        $query = "SELECT URL FROM imageR_TB AS ir
            INNER JOIN room_TB AS r ON ir.Rno = r.Rno
            INNER JOIN hotel_TB AS h ON r.Hno = h.Hno
            WHERE h.Hno = ? AND r.Rno = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Hno, $Rno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while($row= $st->fetch()) {
            array_push($res, $row["URL"]);
        }
        $st=null;$pdo = null;

        return $res;

    }
    /* ************************************************************************* */
    
    //호텔 필터링 (Hno, Bed)
    function hotel_filter($Name, $Sdate, $Bed) {
        $pdo = pdoSqlConnect();
        $like = preg_replace("/\s+/", "", $Name);

        $query = "SELECT DISTINCT H.Hno, H.Name, H.Location, H.lat, H.lng, H.Sdate, H.Edate, D.Percentage, D.Priced, R.Price
        FROM discount_TB AS D
        INNER JOIN room_TB AS R ON R.Rno = D.Rno
        INNER JOIN hotel_TB AS H ON H.Hno = R.Hno
        WHERE H.Location LIKE ? AND R.Bed = ? AND H.Sdate BETWEEN ? AND ?
        AND D.Percentage = (SELECT MAX(d.Percentage) 
            FROM discount_TB AS d
            INNER JOIN room_TB AS r ON r.Rno = d.Rno
            INNER JOIN hotel_TB AS h ON h.Hno = r.Hno
            WHERE h.Hno = H.Hno
            AND r.Booked = ? AND d.Booked = ? AND d.Deleted = ?) 
        AND CAST(REPLACE(D.Priced, ',' ,'') AS UNSIGNED) = (SELECT MIN(CAST(REPLACE(dd.Priced, ',' ,'') AS UNSIGNED))
                FROM discount_TB AS dd
                INNER JOIN room_TB AS rr ON rr.Rno = dd.Rno
                INNER JOIN hotel_TB AS hh ON hh.Hno = rr.Hno
                WHERE hh.Hno = H.Hno
                AND rr.Booked = ? AND dd.Booked = ? AND dd.Deleted = ?)";

        $st = $pdo->prepare($query);
        //$st->execute(["%$like%", $Bed, '2019-04-21', '2019-04-22', 'N', 'N', 'N', 'N', 'N', 'N']);
        $st->execute(["%$like%", $Bed, $Sdate, (string)date("Y-m-d", strtotime($Sdate.'+7 days')),'N', 'N', 'N', 'N', 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        while($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Hno = $row["Hno"];
            $elements->Name = $row["Name"];
            $elements->Location = $row["Location"];
            $elements->lat = $row["lat"];
            $elements->lng = $row["lng"];
            $elements->Sdate = $row["Sdate"];
            $elements->Edate = $row["Edate"];
            $elements->Percentage = $row["Percentage"];
            $elements->Price = $row["Price"];
            $elements->discounted_Price = $row["Priced"];
            $elements->Image = image_add($row["Hno"]);

            array_push($res, $elements);
        }

        $elements=null;$st=null;$pdo = null;

        return $res;
        
    }

    /* ************************************************************************* */

    //예약하기
    function book($Rno, $FName, $LName, $Email, $Sdate, $Edate){
        $pdo = pdoSqlConnect();

        $query = "INSERT INTO book_TB(Rno, Email, FName, LName, Sdate, Edate)
            SELECT ?, ?, ?, ?, ?, ? FROM DUAL
            WHERE NOT EXISTS (SELECT * FROM book_TB WHERE Rno = ? AND Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Rno, $Email, $FName, $LName, $Sdate, $Edate, $Rno, 'N']);

        /* ************************************************************************* */

        $sqlR = "UPDATE room_TB SET Booked = ? WHERE Rno = ?";

        $stR = $pdo->prepare($sqlR);
        $stR->execute(['Y', $Rno]);

        /* ************************************************************************* */

        $sqlD = "UPDATE discount_TB SET Booked = ? WHERE Rno = ?";

        $stD = $pdo->prepare($sqlD);
        $stD->execute(['Y', $Rno]);

        $stR=null;$stD=null;$st=null;$pdo = null;

        return true;

    }

    //예약확인
    function book_check($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT H.Name, H.Location, H.Ratings, H.Sdate, H.Edate, R.Rno, R.Grade, R.Bed, B.Email, B.FName, B.LName 
            FROM book_TB AS B 
            INNER JOIN room_TB AS R ON R.Rno = B.Rno 
            INNER JOIN hotel_TB AS H ON H.Hno = R.Hno 
            WHERE B.Email = ? AND B.Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;

    }

    //예약취소
    function book_cancel($Rno, $Email){
        $pdo = pdoSqlConnect();
        $query = "UPDATE book_TB SET Deleted = ? WHERE Rno = ? AND Email = ?";

        $st = $pdo->prepare($query);
        $st->execute(['Y', $Rno, $Email]);

        /* ************************************************************************* */

        $sqlR = "UPDATE room_TB SET Booked = ? WHERE Rno = ?";

        $stR = $pdo->prepare($sqlR);
        $stR->execute(['N', $Rno]);

        /* ************************************************************************* */

        $sqlD = "UPDATE discount_TB SET Booked = ? WHERE Rno = ?";

        $stD = $pdo->prepare($sqlD);
        $stD->execute(['N', $Rno]);

        $stR=null;$stD=null;$st=null;$pdo = null;

        return true;

    }

    /* ************************************************************************* */

    //날짜 특가호텔 유무 예외처리
    function Validdate($Sdate, $Edate) {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM hotel_TB WHERE Sdate BETWEEN ? AND ?";

        $st = $pdo->prepare($query);
        $st->execute([$Sdate, $Edate]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        if($res == NULL) {
            return false;
        }

        return true;
    }

    //예약 유무 예외처리
    function Validbook($Rno) {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM book_TB WHERE Rno = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Rno, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        if($res == NULL) {
            return true;
        }

        return false;
    }

    //호텔 존재 유무 예외처리
    function ValidHotel($Hno) {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM hotel_TB WHERE Hno = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Hno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        if($res == NULL) {
            return true;
        }

        return false;
    }

    //방 존재 유무 예외처리
    function ValidRoom($Hno, $Rno) {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM room_TB WHERE Hno = ? AND Rno = ? AND Booked = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Hno, $Rno, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        if($res == NULL) {
            return true;
        }

        return false;
    }

    function instruction(){

        /* 1. 80,000이하 특가 호텔 
         * - 할인된 호텔 가격에 따른 목록
         * - 호텔 중에서도 할인율이 높은 룸을 대표로 데이터 출력
         * 
         * 2. 일일 특가 호텔 목록 
         * - 업로드된 날짜가 오늘이며, 40%이상의 할인을 받은 호텔 목록 
         * - 호텔 중에서도 할인율이 높은 룸을 대표로 데이터 출력
         * 
         * 3. 마감 특가 호텔 목록
         * - 시작일이 현재 날짜부터 일주일 기간 내에 있는 호텔 목록
         * 
         * 4. 특가 호텔 방 상세 정보
         * - 선택한 호텔에 따라 할인중인 방의 정보에 대한 데이터 출력*/

         /* ************************************************************************* */

         /* 5. 전체 호텔 목록
          * - 모든 호텔에 대한 정보 출력
          * - 호텔 중에서도 가장 낮은 가격의 룸을 대표로 데이터 출력 
          * - 할인과 상관없는 원가 데이터 출력
          *
          * 6. 전체 방 목록
          * - 해당 호텔에 대해 예약이 안되어있는 모든 룸 데이터 출력
          * - 할인과 상관없는 원가 데이터 출력
          *
          * 7. 호텔 이미지 목록
          * 8. 방 이미지 목록
          *
          * 9. 호텔 필터링
          * - 목적지(나라명, 호텔 이름), 체크 인 날짜, 인원 수(침대)에 맞게 출력 
          *
          * 10. 예약하기
          * - 룸넘버, 성, 이름, 체크 인 날짜, 체크 아웃 날짜에 따라 예약
          *
          * 11. 예약확인
          *
          * 12. 예약취소
          * - 룸넘버에 따른 예약 취소
          */

          //UPDATE discount_TB SET Upload = "2019-04-06";
          /*
          //페이징 처리 예시
          $query = "SELECT * FROM (
            SELECT @ROWNUM := @ROWNUM +1 AS NUM, HURL FROM (SELECT @ROWNUM := 0) R, imageH_TB WHERE Hno = ?)
            A WHERE NUM > 0";
        */
    }