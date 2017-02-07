<?php
return array(
	//'配置项'=>'配置值'
    'LAYOUT_ON'             =>  true, // 是否启用布局
    'LAYOUT_NAME'           =>  'Layout/layout', // 当前布局名称 默认为layout
    'DEFAULT_THEME'         => '',// 获取模板主题名称cookie('think_template')
    'TMPL_DETECT_THEME'    => false,// 自动侦测模板主题
    'THEME_LIST'            => '',//模板主题列表

    'URL_HTML_SUFFIX'       =>  '',  // URL伪静态后缀设置

    'TMPL_PARSE_STRING'     => array(
        '__JS__'  => __ROOT__.'/Public/'.MODULE_NAME.'/js',
        '__CSS__' => __ROOT__.'/Public/'.MODULE_NAME.'/css',
        '__IMG__' => __ROOT__.'/Public/'.MODULE_NAME.'/img',
        '__FONT__' => __ROOT__.'/Public/'.MODULE_NAME.'/font',

        '__PLUGIN__'  => __ROOT__.'/Public/'.MODULE_NAME.'/plugin',
    ),
);