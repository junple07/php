<?php

namespace Img\php;
# 红色是需要根据自己的实际情况修改目录的点
error_reporting(E_ALL);

require_once '/Users/zhanglei/Documents/webdev/github/thrift/lib/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;

$GEN_DIR = realpath(dirname(__FILE__) . '/../') . '/gen-php';
$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', '/Users/zhanglei/Documents/webdev/github/thrift/lib');
$loader->registerDefinition('Img', $GEN_DIR);
$loader->register();


use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;
use Thrift\Exception\TException;
use Thrift\Transport\THttpClient;

try {
    if (array_search('--http', $argv)) {
        $socket = new THttpClient('localhost', 80, '/github/thrift/php/server.php'); // 实际的server文件地址
    } else {
        $socket = new TSocket('127.0.0.1', 80);
    }
    $transport = new TBufferedTransport($socket, 1024, 1024);
    $protocol = new TBinaryProtocol($transport);
    $client = new \Img\ImgInfoClient($protocol);
    $transport->open();
    
    $ret = $client->getimgInfo('BBBBBBBBBBBBBBBBBBBB');
echo 'adf';
    echo $ret;
    echo "<br /> \r\n";

    $transport->close();
} catch (TException $tx) {
    print 'TException: ' . $tx->getMessage() . "\n";
}
