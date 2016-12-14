<?php
// +----------------------------------------------------------------------
// | wxapi [微信接口] ========= 需在入口引用当前配置文件
// +----------------------------------------------------------------------
// | SDK:消息-SDK、普通接口、JS-SDK.1.0
// +----------------------------------------------------------------------
// | Date: 2016/7/13
// +----------------------------------------------------------------------
// | Author: virgo <1131762828@qq.com>
// +----------------------------------------------------------------------

//定义微信api的路径
define('__WXAPI__', dirname(__FILE__)."/");


//微信 消息-SDK 常量配置 ========= MsgBase.class.php、MsgHandler.class.php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
 * 是否开启消息SDK调试
 */
define('WX_MSG_DEBUG',true);    //定义是否开启调试模式


//微信 普通接口 常量配置 ========= WxBase.class.php、(...)
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * SESSION数据存储配置
 */
define('WX_KEY',"wechat");      //定义存取相应微信数据在$_SESSION的键值：$_SESSION[WX_KEY]

define('WX_EXPIRES',7000);      //expires_in，定义签名和令牌的最长有效期（一定要小于7200秒）

/*
 * 调试和限制的配置
 */
define('WX_DEBUG',false);    //定义是否开启调试模式

define('WX_LOG_PATH','./Application/Runtime/wxapi.error.log');    //定义是否开启日志

define('WX_ERROR_LOG',true);    //定义是否开启日志

define('WX_CHECK_STATE',false); //定义是否验证登录时传递的state

/**
 * 从数据库或缓存中获取公众号的全局唯一票据access_token
 */
//返回error 如果为true则为不合法，false则为超时
function wx_token_get($akey)
{
    $sid = session('schoolid');
    if ($sid) {
        $key ='school' .  $sid . 'access_token';
        $token = S($key);
        if (!$token) {
            $m = M('school_wx');
            $token = $m->findObj(array('akey' => $akey), 'access_token,token_ctime');
        }

        if ($token) {
            if (!empty($token['token_ctime']) && WX_EXPIRES + $token['token_ctime'] > time()) {
                if (isset($m)) S($key, $token, WX_EXPIRES);
                return $token['access_token'];
            }
            return array('error' => false);
        }
    }

    return array('error' => true);
}

//返回存储是否成功
function wx_token_set($akey,$access_token)
{
    $sid = session('schoolid');
    if ($sid) {
        $token = array('access_token' => $access_token, 'token_ctime' => time());
        S('school' . $sid . 'access_token', $token, WX_EXPIRES);
        $m = M('school_wx');
        $res = $m->setObj(array('akey' => $akey), $token);
        return !!$res;
    }

    return false;
}


//微信 JS-SDK 常量配置 ========= BaseHandler
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

define('WX_JS_DEBUG',false);    //定义是否开启调试模式

/**
 * 定义需要使用的JS接口列表
 * @link http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html#.E9.99.84.E5.BD.952-.E6.89.80.E6.9C.89JS.E6.8E.A5.E5.8F.A3.E5.88.97.E8.A1.A8
 */
define('WX_JS_APILIST',implode(',',array(
    /*'onMenuShareTimeline',//分享到朋友圈
    'onMenuShareAppMessage',//分享给朋友
    'onMenuShareQQ',//分享到QQ
    'onMenuShareWeibo',//分享到腾讯微博
    'onMenuShareQZone',//分享到QQ空间*/

    'startRecord',//开始录音
    'stopRecord',//停止录音
    'onVoiceRecordEnd',//监听录音自动停止
    'playVoice',//播放语音
    //'pauseVoice',//暂停播放
    'stopVoice',//停止播放
    'onVoicePlayEnd',//监听语音播放完毕
    'uploadVoice',//上传语音
    //'downloadVoice',//下载语音

    'chooseImage',//拍照或从手机相册中选图
    'previewImage',//预览图片
    'uploadImage',//上传图片
    //'downloadImage',//下载图片

    /*'translateVoice',//识别音频并返回识别结果
    'getNetworkType',//获取网络状态
    'openLocation',//使用微信内置地图查看位置
    'getLocation',//获取地理位置*/

    /*'hideOptionMenu',//隐藏右上角菜单
    'showOptionMenu',//显示右上角菜单
    'hideMenuItems',//批量隐藏功能按钮
    'showMenuItems',//批量显示功能按钮
    'hideAllNonBaseMenuItem',//隐藏所有非基础按钮
    'showAllNonBaseMenuItem',//显示所有功能按钮
    'closeWindow',//关闭当前网页窗口
    'scanQRCode',//调起微信扫一扫*/

    /*'chooseWXPay',//发起一个微信支付请求
    'openProductSpecificView',//跳转微信商品页
    'addCard',//批量添加卡券
    'chooseCard',//拉取适用卡券列表并获取用户选择信息
    'openCard',//查看微信卡包中的卡券*/
)));

/**
 * 从数据库或缓存中获取公众号用于调用微信JS接口的临时票据
 */
//返回error 如果为true则为不合法，false则为超时
function wx_ticket_get($akey)
{
    $sid = session('schoolid');
    if ($sid) {
        $key ='school' .  $sid . 'jsapi_ticket';
        $ticket = S($key);
        if (!$ticket) {
            $m = M('school_wx');
            $ticket = $m->findObj(array('akey' => $akey), 'jsapi_ticket,ticket_ctime');
        }

        if ($ticket) {
            if (!empty($ticket['ticket_ctime']) && WX_EXPIRES + $ticket['ticket_ctime'] > time()) {
                if (isset($m)) S($key, $ticket, WX_EXPIRES);
                return $ticket['jsapi_ticket'];
            }
            return array('error' => false);
        }
    }

    return array('error' => true);
}

//返回存储是否成功
function wx_ticket_set($akey,$jsapi_ticket)
{
    $sid = session('schoolid');
    if ($sid) {
        $ticket = array('jsapi_ticket' => $jsapi_ticket, 'ticket_ctime' => time());

        S('school' . $sid . 'jsapi_ticket', $ticket, WX_EXPIRES);

        $m = M('school_wx');
        $res = $m->setObj(array('akey' => $akey), $ticket);
        return !!$res;
    }

    return false;
}