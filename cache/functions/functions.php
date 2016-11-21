<?php

define('CACHE_DIR', dirname(dirname(__FILE__)) . '/cache/');
define('CACHE_CONF', dirname(dirname(__FILE__)) . '/config/cache.yml');
define('TEMPLATES', dirname(dirname(__FILE__)) . '/templates/');

/**
 * @param type $log 写入内容的文件
 * @param type $content 需要写入的内容
 * @todo 将内容写入到指定文件去
 */
function writer($log, $content){
    $fp = fopen($log, 'w+');
    fwrite($fp, $content);
    fclose($fp);
}

/*
 * 取出配置文件里面的内容删除配置文件中的注释
 */
function get_conf(){
    // 如果没有配置文件
    if(!file_exists(CACHE_CONF)) return false;
    
    $tmp_content    = file_get_contents(CACHE_CONF);
    if(empty($tmp_content)) return false;
    $tmp_arr        = explode("\r\n", $tmp_content);
    $contents       = array();
    foreach($tmp_arr as $line){
        // 如果第一个字符, 是#开头的, 则删除这一行
        if(strpos('#', trim($line)) == 0){
            continue;
        }
        $contents[] = $line;
    }
    
    $arr = explode('_', implode("\r\n", $contents));
    if(empty($arr[0])) array_shift($arr);
    
    return $arr;
}

/**
 * @params $component 组件名称
 * @todo 通过config文件的cache.yml文件取得component组件的配置信息
 * @return 返回通过设置的cache文件
 */
function get_cache($component){
    // 配置文件的内容, 数组格式
    $contents = get_conf();
    print_r($contents);die;

    $component_data = array();

    if(is_array($contents)){
        foreach($contents as $value){
            $index_arr = explode("\r\n", trim($value));
            $first_arr = array_shift($index_arr);
            $component_index = rtrim($first_arr, ":");
            if($component == $component_index){
                if(is_array($index_arr)){
                    foreach($index_arr as $_k => $_v){
                        $arr = explode(":", trim($_v));
                        $component_data[trim($arr[0])] = trim($arr[1]);
                    }
                }
            }
        }
    }
    //print_r($component_data);
    if(empty($component_data) || !isset($component_data['enabled']) || $component_data['enabled'] != 'on'){
        $component_data['cache_file'] = TEMPLATES . "_" . $component . ".php";
    }else{
        $component_data['cache_file'] = CACHE_DIR . md5(base64_encode($component)) . ".cache";
    }
    return $component_data;

}


/**
 * @params $component 组件名称
 * @todo 将浏览器缓存打开, 放入cache文件中
 * 返回cache文件
 */
function set_cache($template, $component){
    $cache_data = get_cache($component);
    if(empty($cache_data) || empty($cache_data['cache_file'])) return false;
    $cache_file = $cache_data['cache_file'];
    
    ob_start();
    include_once($template);
    $ob_content = ob_get_contents();
    writer($cache_file, $ob_content);
    ob_end_flush();
}

/**
 * @params $component 组件名称
 * @todo 通过组建名称得到缓存配置信息
 *     通过配置信息 区分是引用源文件还是引用缓存文件
 *     如果引用缓存文件, 判断组建缓存配置信息的lifetime是否过期, 如果过期重新设置缓存, 未过期引用缓存文件
 */
function include_component($component){
    $template = TEMPLATES . "_" . $component . ".php";
    if(!file_exists($template)) throw new Exception("component $component is not exists");
    
    $cache_data = get_cache($component);
   
    if($cache_data['enabled'] != 'on'){
        $cache_data['cache_file'] = str_replace("\\", "/", $cache_data['cache_file']);
        include_once($cache_data['cache_file']);
    }

    if(file_exists($cache_data['cache_file']) && (filemtime($cache_data['cache_file']) + $cache_data['lifetime']) > time()){
        // 使用缓存
        if(!file_exists($cache_data['cache_file'])) set_cache($template, $component);
        include_once($cache_data['cache_file']);
    }else{
        // 设置缓存
        set_cache($template, $component);
    }
}

?>