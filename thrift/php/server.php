<?php
namespace Img\php;

error_reporting(E_ALL);

require_once '/Users/zhanglei/Documents/webdev/github/thrift/lib/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;

$GEN_DIR = realpath(dirname(__FILE__).'/../').'/gen-php';

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', '/Users/zhanglei/Documents/webdev/github/thrift/lib');
$loader->registerDefinition('Img', $GEN_DIR);
$loader->register();
if (php_sapi_name() == 'cli') {
  ini_set("display_errors", "stderr");
}

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TPhpStream;
use Thrift\Transport\TBufferedTransport;

class ImgInfoHandler implements \Img\ImgInfoIf {
  protected $log = array();

  public function getimgInfo($name) {
      return $name .  ' ==>   client Request server is ok!';
  }

};

header('Content-Type', 'application/x-thrift');
if (php_sapi_name() == 'cli') {
  echo "\r\n";
}

$handler = new \Img\php\ImgInfoHandler();
$processor = new \Img\ImgInfoProcessor($handler);

$transport = new TBufferedTransport(new TPhpStream(TPhpStream::MODE_R | TPhpStream::MODE_W));
$protocol = new TBinaryProtocol($transport, true, true);

$transport->open();
$processor->process($protocol, $protocol);
$transport->close();
