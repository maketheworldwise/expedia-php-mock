<?php

require './model/pdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//error_reporting(E_ALL); ini_set("display_errors", 1);

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    //Main Server API
    $r->addRoute('GET', '/expedia', 'index');
    $r->addRoute('GET', '/expedia/test', 'test');

    // 관리자 API
    $r->addRoute('POST', '/expedia/add_hotel', 'add_hotel');
    $r->addRoute('POST', '/expedia/add_hotel_image', 'add_hotel_image');
    $r->addRoute('POST', '/expedia/add_room', 'add_room');
    $r->addRoute('POST', '/expedia/add_room_image', 'add_room_image');
    $r->addRoute('POST', '/expedia/add_discount', 'add_discount');
    
    // 회원가입, 회원탈퇴, 로그인 API
    $r->addRoute('POST', '/expedia/user', 'user');
    $r->addRoute('DELETE', '/expedia/user', 'user_delete');
    $r->addRoute('POST', '/expedia/login', 'login');

    //80,000 이하 특가 호텔 목록, 일일 특가 호텔 목록, 마감 특가 호텔 목록, 상세정보 API
    $r->addRoute('GET', '/expedia/discounted_80000', 'discounted_80000');
    $r->addRoute('GET', '/expedia/discounted_today', 'discounted_today');
    $r->addRoute('GET', '/expedia/discounted_fin', 'discounted_fin');
    $r->addRoute('GET', '/expedia/discounted_more', 'discounted_more');

    //전체 호텔 목록, 전체 방 목록 API
    $r->addRoute('GET', '/expedia/hotel', 'hotel');
    $r->addRoute('GET', '/expedia/room', 'room');

    //호텔 이미지 목록, 방 이미지 목록 API
    $r->addRoute('GET', '/expedia/hotel_image', 'hotel_image');
    $r->addRoute('GET', '/expedia/room_image', 'room_image');

    //호텔 필터링 API
    $r->addRoute('GET', '/expedia/hotel_filter', 'hotel_filter');

    //예약, 예약확인, 예약취소 API
    $r->addRoute('POST', '/expedia/book', 'book');
    $r->addRoute('GET', '/expedia/book', 'book_check');
    $r->addRoute('DELETE', '/expedia/book', 'book_cancel');

    //토큰
    $r->addRoute('GET', '/expedia/token', 'token');
    
    //FCM
    $r->addRoute('GET', '/expedia/fcm', 'sendfcm_new');

//    $r->addRoute('GET', '/logs/error', 'ERROR_LOGS');
//    $r->addRoute('GET', '/logs/access', 'ACCESS_LOGS');


//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs =  new Logger('BIGS_ACCESS');
$errorLogs =  new Logger('BIGS_ERROR');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1]; $vars = $routeInfo[2];
        require './controller/mainController.php';

        break;
}
