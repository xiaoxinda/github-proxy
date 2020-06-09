<?php

use \Workerman\Worker;

$task_worker = new Worker('text://127.0.0.1:16345');
// task进程数可以根据需要多开一些
$task_worker->count = 10;
$task_worker->name = 'TaskWorker_github';
$datatime = date("Ymd");
$webDir = dirname(dirname(dirname(dirname(__FILE__)))) . "/staticweb/source/".$datatime;

$task_worker->onMessage = function ($connection, $task_data) use ($webDir,$datatime) {
    $dataArr = json_decode($task_data, true);
    if(!$dataArr){
        return;
    }
    if(isset($dataArr['type'])){
        switch($dataArr['type']){
            case "1":
                $pdo = new PDO('sqlite:' . dirname(dirname(dirname(__FILE__))) . '/sql');
                $pdo->exec("UPDATE statistics SET down_num=down_num+1 where id=1");
                $connection->send("true");
                $pdo = null;
                return true;
            break;

            case "2":
                $pdo = new PDO('sqlite:' . dirname(dirname(dirname(__FILE__))) . '/sql');
                $data = $pdo->query("select down_num from statistics where id=1")->fetch();
                $down_num = $data[0];
                $connection->send(json_encode([
                    'status' => 0,
                    'msg' =>"完成",
                    'data' =>[
                        'sta' => 4,
                        'down_num' => $down_num
                    ]
                ],JSON_UNESCAPED_UNICODE) . "\r\n");
                $pdo = null;
                $connection->send("true");
                return true;
                break;
        }
    }
    if (preg_match("/%|\"|\.\.|\\\|\?|\||`|&|reboot|rm |mv |shutdown|<|>|sh |;|'/",$dataArr['url'])
        ||
        (
            !preg_match("/^((https|http):\/\/)?([\w\.\s\S\/\-\_^]+?)\.(tar\.gz|gz|tgz|tar\.bz2|bz2|msi|tar|zip|tar\.xz|tar\.z|rpm|deb|exe|apk|dmg|7z|rar)$/iu",$dataArr['url'],$ext)
            && !preg_match("/^https:\/\/github.com\/([\w\/\-\.\_]+)$/iu", $dataArr['url']
            )
        )
    ) {
        $connection->send(json_encode(["status" => 1, "msg" => "请输入正确的github地址：仅支持https协议 或<br> （gz|tar.gz|bz2|tar|tar.bz2|zip|tar.xz|tar.z|rpm|deb|rar）格式的压缩包下载链接"]) . "\r\n");
        $connection->send("true");
        return;
    }
    if (!is_dir($webDir)) {
        mkdir($webDir, 0777);
    }
    if(stristr($dataArr['url'],'github') &&  (!isset($ext) || !$ext)) {
        //github的检出、打包
        $connection->send(json_encode([
                'status' => 0,
                'msg' => "正在检出代码",
                'data' => [
                    'sta' => 1,
                ]
            ], JSON_UNESCAPED_UNICODE) . "\r\n");
        $git_dir_path = md5($dataArr['url']);
        if (!is_dir($webDir . "/" . $git_dir_path) && !is_file($webDir . "/{$git_dir_path}.tar")) {
            system('cd ' . $webDir . ';git clone --depth=50 "' . $dataArr['url'] . '" '  . $git_dir_path);
        }
        $connection->send(json_encode([
                'status' => 0,
                'msg' => "正在打包，请稍后",
                'data' => [
                    'sta' => 2,
                ]
            ], JSON_UNESCAPED_UNICODE) . "\r\n");
        if (!is_file($webDir . "/{$git_dir_path}.tar")) {
            if(!is_dir($webDir."/".$git_dir_path)){
                $connection->send(json_encode([
                        'status' => 1,
                        'msg' => "您输入的链接可能是不支持的后缀，如果是git仓储，请确认链接正确",
                        'data' => []
                    ], JSON_UNESCAPED_UNICODE) . "\r\n");
                $connection->send("true");
                return;
            }
            system('cd ' . $webDir . ";tar -cvf {$git_dir_path}.tar {$git_dir_path} ");
            system("rm -rf " . $webDir . "/" . $git_dir_path);
        }
        $pdo = new PDO('sqlite:' . dirname(dirname(dirname(__FILE__))) . '/sql');
        $pdo->exec("insert into file_info ('path', 'addtime') VALUES ('" . $webDir . "/{$git_dir_path}.tar" . "','" . time() . "')");
        $pdo = null;
        $connection->send(json_encode([
                'status' => 0,
                'msg' => "下载压缩包",
                'data' => [
                    'sta' => 3,
                    'url' => '/source/' . $datatime . "/$git_dir_path.tar",
                ]
            ], JSON_UNESCAPED_UNICODE) . "\r\n");
        $connection->send("true");
    }else{
        //普通的压缩包下载处理
        $connection->send(json_encode([
                'status' => 0,
                'msg' => "准备中，请耐心等待~☕️",
                'data' => [
                    'sta' => 2,
                ]
            ], JSON_UNESCAPED_UNICODE) . "\r\n");
        $fileExt = end($ext);
        $fileName = md5($dataArr['url']) . ".{$fileExt}";
        system('cd ' . $webDir . '; wget "'.$dataArr['url'] . '" -O '.$fileName);
        $pdo = new PDO('sqlite:' . dirname(dirname(dirname(__FILE__))) . '/sql');
        $pdo->exec("insert into file_info ('path', 'addtime') VALUES ('" . $webDir . "/{$fileName}" . "','" . time() . "')");
        $pdo = null;
        $connection->send(json_encode([
                'status' => 0,
                'msg' => "下载压缩包",
                'data' => [
                    'sta' => 3,
                    'url' => '/source/' . $datatime . "/{$fileName}",
                ]
            ], JSON_UNESCAPED_UNICODE) . "\r\n");
        $connection->send("true");

    }



};
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
