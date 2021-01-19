<?php

use Workerman\Worker;
use Workerman\Lib\Timer;

// worker
$webDirSource = dirname(dirname(dirname(__FILE__)));
$worker = new Worker();
$worker->name = 'FileMonitor';
$worker->reloadable = false;
$worker->onWorkerStart = function () {
    global $webDirSource;
    Timer::add(300, 'check_files_remove', $webDirSource);
};

// check files func
function check_files_remove($webDirSource)
{

    try {
        $pdo = new PDO('sqlite:' . $webDirSource . '/sql');
        $data = $pdo->query("select * from file_info where addtime < '" . (time() - 60 * 60 * 2 + 5) . "'")->fetchAll();
        foreach ($data as $item) {
            if (is_file($item['path'])) {
                unlink($item['path']);
            }
            $pdo->exec("delete from file_info where id=" . $item['id']);
        }
        $pdo = null;
    } catch (\Throwable $e) {
        print_r([
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
    return;
}
