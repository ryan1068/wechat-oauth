<?php


/** PHP300Framework默认入口 version:2.5.3 */

if(isset($_SERVER["HTTP_ORIGIN"])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: *");
}
if($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"])) {
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
    }
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}

header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Headers:Origin, Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age:86400');
header('P3P: CP="CAO DSP COR CUR ADM DEV TAI PSA PSD IVAi IVDi CONi TELo OTPi OUR DELi SAMi OTRi UNRi PUBi IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE GOV"');

if (substr(PHP_VERSION, 0, 3) < 5.4) die('<meta charset="UTF-8">PHP300:请将PHP版本切换至5.3以上运行!');

/** 引入框架文件 */
require '../Framework/frame.php';

/** @var object 实例化应用 $app */
$app = new Framework\App();

/** 设定默认访问(应用,控制器,方法) */
$app()->get('Visit')->bind(array('Home', 'Index', 'index'));

/** 是否调试模式(true => 调试,false => 线上) */
$app()->get('Running')->isDev(true);

/** 运行应用 */
$app()->run();