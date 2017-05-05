<?php
$serv = new swoole_server('127.0.0.1', 9501);

$serv->set(array(
    'daemonize'     => true,
    'reactor_num'   => 2,
    'worker_num'    => 2
));

$serv->on('connect', function($serv, $fd){
    echo "onConnect \r\n";
});

$serv->on('receive', function($serv, $fd, $from_id, $data){
    $serv->send($fd, "server: " . $data);
});

$serv->on('close', function($serv, $fd){
    echo "onClose \r\n";
});

$serv->start();
