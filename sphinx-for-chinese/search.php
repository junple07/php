<?php
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '我只能随便测试一下咯';

// 实例化sphinx类, 用的是php sphinx扩展
$s = new SphinxClient;
$s->setServer('127.0.0.1', 9312);

// 设置匹配的模式, SPH_MATCH_ANY, 切分的词, 任何一个匹配到就算
$s->setMatchMode(SPH_MATCH_ANY);
$s->SetSortMode('SPH_SORT_ATTR_DESC');

// place_poi这张表里面, 状态都可用的数据
$s->SetFilter('status', 1);
$s->AddQuery($keyword, 'index_qyer_place_poi');

// 重置一下, 否则之前的SetFilter都有效
$s->ResetFilters();

// place_tag这张表里面, 状态都可用的数据
$s->SetFilter('state', 1);
$s->AddQuery($keyword, 'index_qyer_place_tag');

// 同时搜index_qyer_place_poi和index_qyer_place_tag这两个索引的数据
$result = $s->RunQueries();
// print_r($result);

// 关闭php和sphinx的链接
$s->close();

if(empty($result)) die;

foreach($result as $value){
    if(empty($value['matches'])) contiune;
    foreach($value['matches'] as $id => $_v){
        $type = $_v['attrs']['spx_type'] == 1 ? 'poi' : 'tag';
        echo sprintf("id: %utype: %s<br />", $id, $type);
    }
}