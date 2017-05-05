<?php
$client = new swoole_client(SWOOLE_SOCK_TCP);

if(!$client->connect('172.1.4.118', 9501, 0.5)){
    die('close error');
}

if(!$client->send('hello world')){
    die('send error');
}

$data = $client->recv();
if(empty($data)){
    die('receive error');
}

echo $data . "\r\n";

$client->close();
