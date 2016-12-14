<?php

include_once __DIR__ . "/base/WxBase.class.php";

/**
 * 模板消息（推送）管理类
 * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277&token=&lang=zh_CN
 */
class TplMsgHandler extends WxBase
{
    const URL = "https://api.weixin.qq.com/cgi-bin/template/";
    //菜单管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param $openid string 接收者openid
     * @param $tplid string 模板ID
     * @param $url string 模板跳转链接
     * @param $data array 模板数据
     * @param $check_res bool|true 是：验证错误返回数组，否：原样返回json字符串
     * @return array
     */
    public function send($openid, $tplid, $url, $tpldata, $check_res = false)
    {
        $param = array('access_token' => $this->get_token());

        $data = array(
            'touser' => $openid,
            'template_id' => $tplid,
            'url' => $url,
            'data' => $tpldata,
        );
        $res = $this->post_data(parent::URL . 'message/template/send', $param, $data, $check_res);
        return $res;
    }
}