<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用目录
define('APP_PATH','./Application/');


// 接口相关配置
define('__API__', dirname(__FILE__).'/Application/Common/Common/');
//require_once __API__.'wxapi/config.php';

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';



define('APP_LOG',true);//是否开启日志
define('LOG_PATH','./Log/');//日志文件夹

/**
 * 写入日志
 * @param string $title
 * @param array $str_arr
 */
function write_log($title,$str_arr,$path = LOG_PATH)
{
    if (APP_LOG) {
        $fp = fopen($path . date('Ymd'), "a");
        if ($fp) {
            $log = date("H:i:s") . '：\t' . $title;
            if (is_array($str_arr)) {
                foreach ($str_arr as $k => $v) {
                    $log .= '\n' . (is_numeric($k) ? '' : $k . '：') . $v;
                }
            } else $log .= '\n' . $str_arr;
            $log .= '\n\n';
            fwrite($fp, $log);
            fclose($fp);
        }
    }
}