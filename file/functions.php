<?php
/**
 * 处理文件的函数, 尝试去写一些
 * @author zhanglei <zhanglei19881228@sina.com>
 * @date 2018-02-27 16:00
 */


// 插入指定行
function insert_specific_line($file, $line, $string = 'i am the insert specific line'){
    if(!file_exists($file) || empty($line) || !is_int($line)) return false;
    
    $system = sprintf("sed -i '%ua\%s' %s", $line - 1, $string . "\n", $file);

    exec($system);
}

// 查找指定行
function select_specific_line($file, $line){
    if(!file_exists($file) || empty($line) || !is_int($line)) return false;
    
    $fp = fopen($file, 'r+');
    
    $step = 0;
    $current_line = 1;
    $content = '';
    while(!feof($fp)){
        fseek($fp, $step, SEEK_SET);
        $step++;
        
        $word = fread($fp, 1);
        $content .= $word;
        
        if($word == "\n"){
            if($current_line == $line){
                break;
            }
            
            // 不是要查找的行, 把content赋值成空
            $content = '';
            $current_line++;
        }
    }
    
    return $content;
}


// 查到指定行的指定字符
function select_specific_word($file, $line, $number){
    if(!file_exists($file) || empty($line) || !is_int($line) || empty($number) || !is_int($number)) return false;
    
    $string = select_specific_line($file, $line);
    
    return $string[$number - 1];
}

/*
 * 大文件排序
 * 新建一个文件, 然后一行一行读取大文件
 * 然后插入到新文件
 * 读原文件一行, 再顺序读新文件, 如果直到读到值小于新文件的那一行, 直接插入那一行的上面一行, 如此循环
 * eg: 原文件读到100, 新文件是1, 3, 101, ..., 当读到101是, 立刻插入到101的上方一行
 */
function write($file, $words){
    $fp = fopen($file, 'a+');
    fwrite($fp, sprintf("%u\n", $words));
    fclose($fp);
}

function sort_file($file){
    if(!file_exists($file)) return false;
    
    $file_tmp = './sort_log';
    if(file_exists($file_tmp)) unlink($file_tmp);
    exec(sprintf('touch %s', $file_tmp));
    
    $fp = fopen($file, 'r');
    
    $x = 0;
    while(!feof($fp)){
        $line = (int)fgets($fp);
        if(empty($line)) continue;
        
        // 第一行为空, 首次, 里面啥都没有, 直接写进去, 文件行不存在sed, 写不进去啊, 只能用php写
        if($x == 0){
            write($file_tmp, $line);
            $x = 1;
            continue;
        }
        
        // 最后比最后一行都大, 直接写入结尾
        $last_line = (int)exec(sprintf('tail -1 %s', $file_tmp));
        if($line >= $last_line){
            write($file_tmp, $line);
            continue;
        }
        
        $i      = 1;
        $fp_tmp = fopen($file_tmp, 'r');
        while(true){
            $line_tmp = (int)fgets($fp_tmp);
            
            // 当原文件小于新文件的当前行, 插入到当前行的前一行
            if($line <= $line_tmp){
                $system = sprintf("sed -i '%ui\%u' %s", $i, $line, $file_tmp);
                exec($system);
                break;
            }
            
            $i++;
            
            if(feof($fp_tmp)) break;
        }
        fclose($fp_tmp);
        
        $x++;
    }
}