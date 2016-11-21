<?php

define('CACHE_DIR', dirname(dirname(__FILE__)) . '/cache/');
define('CACHE_CONF', dirname(dirname(__FILE__)) . '/config/cache.php');
define('TEMPLATES', dirname(dirname(__FILE__)) . '/templates/');

/**
 * @param type $log 写入内容的文件
 * @param type $content 需要写入的内容
 * @todo 将内容写入到指定文件去
 */
function put_cache($log, $content){
    if(empty($log) || empty($content)) return false;
    
    $fp = fopen($log, 'w+');
    fwrite($fp, $content);
    fclose($fp);
}

/**
 * @params $key         组件的类型
 * @params $component   组件名称
 * @todo 通过配置文件取出当前组件的配置信息
 * @return array()
 */
function get_cache_config($key, $component){
    if(empty($key) || empty($component)) return false;
    
    // 配置文件是否存在
    if(!file_exists(CACHE_CONF)) return false;
    
    // 配置文件内容
    $confs = include_once(CACHE_CONF);
    
    // 配置文件不存在此类型
    if(empty($confs[$key])) return false;
    
    return !empty($confs[$key][$component]) ? $confs[$key][$component] : false;
}


/**
 * @params $key         组件类型
 * @params $component   组件名称
 * @todo 将浏览器缓存打开, 放入cache文件中
 * @return boolean
 */
function set_cache($key, $component){
    if(empty($key) || empty($component)) return false;
    
    // 真实的模板文件
    $template = get_template($key, $component);
    if(empty($template)) return false;
    
    $template_cache = get_cache_template($key, $component);
    
    ob_start();
    include_once($template);
    $ob_content = ob_get_contents();
    put_cache($template_cache, $ob_content);
    ob_end_flush();
    
    return true;
}

// 通过类型以及组件名称取出缓存文件
function get_cache_template($key, $component){
    if(empty($key) || empty($component)) return false;
   
    // 缓存文件的生成方式
    $cache_template = sprintf('%s%s.cache', CACHE_DIR, md5(base64_decode(sprintf('%s_%s', $key, $component))));
    
    return $cache_template;
}

// 取出真实的模板文件
function get_template($key, $component){
    if(empty($key) || empty($component)) return false;
    
    $template = sprintf('%s%s_%s.php', TEMPLATES, $key, $component);
    
    if(!file_exists($template)) return false;
    
    return $template;
}

/**
 * @params $component 组件名称
 * @todo 通过组建名称得到缓存配置信息
 *     通过配置信息 区分是引用源文件还是引用缓存文件
 *     如果引用缓存文件, 判断组建缓存配置信息的lifetime是否过期, 如果过期重新设置缓存, 未过期引用缓存文件
 *     设置不同的参数, 方便调试信息 $_GET['component'] = 1, 则使用真实的内容, 否则使用缓存的内容
 */
function include_component($key, $component){
    // 检查模板文件是否存在
    $template = get_template($key, $component);
echo $template;die;
    if(!empty($template)) return false;
    
    // 如果调试真是的文件内容, 返回原文件
    if(!empty($_GET['component'])){
        return include_once($template);
    }
    
    // 得到当前组件的配置信息, 如果配置信息没有当前的类型以及组件, 返回真实原文件
    $cache_data = get_cache_config($key, $component);
    if(empty($cache_data)){
        return include_once($template);
    }

    // 不使用缓存, 取出真实模板文件
    if($cache_data['enabled'] != 'on'){
        return include_once($template);
    }

    // 以下就是使用缓存的情况
    $cache_template = get_cache_template($key, $component);
    if(file_exists($cache_template) && (filemtime($cache_template) + $cache_data['lifetime']) > time()){
        // 当缓存文件的修改时间 + 配置缓存的时间 > 当前时间, 则使用缓存文件的内容
        return $cache_template;
    }else{
        // 使用缓存, 只是过期了而已, 或者缓存文件不存在, 需要重新设置缓存文件, 并返回真实的文件
        set_cache($key, $component);
        return $template;
    }
}