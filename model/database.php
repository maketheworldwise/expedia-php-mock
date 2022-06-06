<?php

function pdoSqlConnect(){
    try {
        $DB_HOST = "127.0.0.1"; $DB_NAME = "Expedia";
        $DB_USER = "admin"; $DB_PW = "ExPedia_10$";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;

    } catch (Exception $e) {
        echo $e->getMessage();
    }

}

define('SERVER_KEY', 'Keys');

function sendfcm_new() {
    $Today = '2019-04-21';
    
    $pdo = pdoSqlConnect();
    $query = "SELECT Token 
        FROM user_TB 
        WHERE Email IN (SELECT Email FROM book_TB WHERE Sdate = ? AND Deleted = ?) AND Deleted = ?";
    
    $st = $pdo->prepare($query);
    $st->execute([$Today, 'N', 'N']);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    //데이터 설정
    foreach($res as $res) {
        $registration_ids[] = $res['Token'];
    }
    $st=null;$pdo = null;

    $header = [
        'Authorization: Key=' . SERVER_KEY,
        'Content-Type: Application/json' 
    ];

    $msg = [
        'title' => "예약 날짜 입니다!",
        'body'  => "예약 내역을 확인해보세요!"
    ];

    $payload = [
        'registration_ids' => $registration_ids,
        'data'  => $msg
    ];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $header
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return $err;
    } 

    return $response;

    /* sudo apt-get install php7.0-curl
     * 우분투 서버 시간이 다르기 때문에 shell에서 date로 현재 시간 확인
     * 시간이 다르다면 tzselect-Asia-South Korea-TZ='Asia/Seoul'; export TZ
     * Or 'sudo dpkg-reconfigure tzdata' 로 설정!
     * 
     * /etc - sudo vi crontab
     * 분/시/일/월/요일
     * 00 09 * * * root /var/www/html/expedia/model/fcm.php
     * 00 09 * * * root /var/www/html/test/expedia/model/fcm.php
     * 0 17 * * * /usr/bin/wget -s -o /dev/null www.kaca5.com/expedia/fcm
     * service cron restart
     * */
}
