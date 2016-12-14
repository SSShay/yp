<?php

include_once __DIR__ . "/base/WxBase.class.php";

/**
 * JsSDK支持，获取票据和创建签名
 */
class JsSDK extends WxBase
{
    /**
     * 获取公众号用于调用微信JS接口的临时票据jsapi_ticket
     * @return mixed
     */
    protected function get_js_ticket()
    {
        //jsapi_ticket是公众号用于调用微信JS接口的临时票据。
        //正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。
        //由于获取jsapi_ticket的api调用次数非常有限，频繁刷新jsapi_ticket会导致api调用受限，
        //影响自身业务，开发者必须在自己的服务全局缓存jsapi_ticket 。
        $url = self::URL . "ticket/getticket";

        if (!WX_DEBUG) {
            $res = wx_ticket_get($this->akey);
            if (is_array($res)) {
                if ($res['error'] === true) {
                    $this->set_error($url, __CLASS__ . ' -> ' . __FUNCTION__, "不合法操作");
                    return false;
                }
            } else return $res;
        }

        $param = array('access_token' => $this->get_token(), 'type' => 'jsapi');

        $res = $this->get_data($url, $param, false);
        $json = json_decode($res, true);
        if (!$json || $json['errcode'] != 0) {
            $this->set_error($url, $json['errcode']);
            return false;
        }

        $jsapi_ticket = $json['ticket'];
        wx_ticket_set($this->akey, $jsapi_ticket);
        return $jsapi_ticket;
    }

    //获取-JS-SDK使用权限签名
    public function get_js_sign($noncestr, $timestamp, $url)
    {
        $param = array(//
            'jsapi_ticket=' . $this->get_js_ticket(),//
            'noncestr=' . $noncestr,//
            'timestamp=' . $timestamp,//
            'url=' . $url,//
        );

        return sha1(implode('&', $param));
    }

    //创建微信jsSDK的配置
    public function create_js_config()
    {
        $timestamp = time();
        $noncestr = md5($timestamp);
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        return array(//
            'debug' => WX_JS_DEBUG,//是否开启调试
            'appId' => $this->akey,//公众号唯一标识
            'timestamp' => $timestamp,//时间戳
            'nonceStr' => $noncestr,//随机字符串
            'signature' => $this->get_js_sign($noncestr, $timestamp, $url),//签名
            'jsApiList' => explode(',', WX_JS_APILIST),//需要使用的JS接口列表
        );
    }
}