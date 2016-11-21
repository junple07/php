<?php
// 每一个模块cache的配置文件, 由于页面众多, 如果一维数组的key, 代表不同的页面
return array(
    'index' => array(
        'top' 		=> array(
            'enabled' 	=> 'on',
            'lifetime'	=> '60'
        ),
        'center'	=> array(
            'enabled' 	=> 'off',
            'lifetime'	=> '60'
        )
    )
);