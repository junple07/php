<?php
require_once('./functions.php');

$file = './log';

// 生成文件
/*
$fp = fopen($file, 'a+');
for($i = 0; $i <= 1000; $i++){
    fwrite($fp, sprintf("%u\n", rand(100000, 999999)));
}
fclose($fp);
*/

// 插入指定行
// insert_specific_line($file, 2);

// 查找指定行
// echo select_specific_line($file, 100) . PHP_EOL;

// 查到指定行的指定字符
// echo select_specific_word($file, 100, 5) . PHP_EOL;

sort_file($file);