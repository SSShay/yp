<?php

include_once __DIR__ . "/base/WxBase.class.php";

/**
 * 微信用户管理类
 * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140839&token=&lang=zh_CN
 */
class UserHandler extends WxBase
{

    const URL = "https://api.weixin.qq.com/sns/";

    /**
     * 微信登录页面，true为扫描二维码的模式，false是微信客户端web页面模式
     * @param bool|false $mode_qrcode
     */
    public function login($mode_qrcode = false)
    {
        if (WX_CHECK_STATE) {
            $state = md5(time());
            $this->set_val('state', $state);    //储存state，防止code被截获，在callback时验证state
        } else {
            $state = "";
        }

        $data = array(
            'appid' => $this->akey,
            'redirect_uri' => $this->callback_url,
            'response_type' => 'code',
            'scope' => $this->scope,
            'state' => $state . "#wechat_redirect"
        );
        $login_url = "https://open.weixin.qq.com/connect/" . ($mode_qrcode ? "qrconnect?" : "oauth2/authorize?") . http_build_query($data);

        header("Location:$login_url");
    }

    /*
     * 登录成功后的回调所需执行的方法,将access_token和openid保存在session;
     *
     * {
     *        "access_token":"ACCESS_TOKEN",
     *        "expires_in":7200,
     *        "refresh_token":"REFRESH_TOKEN",
     *        "openid":"OPENID",
     *        "scope":"SCOPE"
     * }
     */
    public function callback()
    {
        $token_url = self::URL . 'oauth2/access_token';
        if (WX_CHECK_STATE)
        {
            if (!isset($_REQUEST["state"]) || $_REQUEST["state"] != $this->get_val( '.state'))
            {
                $this->set_error($token_url, __CLASS__ . ' -> ' . __FUNCTION__, "参数state有误");
                return false;
            }
        }

        if (!isset($_REQUEST["code"]) || !$_REQUEST["code"])
        {
            $this->set_error($token_url, '40029');
            return false;
        }

        $data = array(//
            'appid' => $this->akey, //
            'secret' => $this->skey, //
            'code' => $_REQUEST["code"], //
            'grant_type' => "authorization_code",//
        );

        $response = $this->get_data($token_url,$data,false);
        $msg = json_decode($response, true);
        if (isset($msg->error))
        {
            $this->set_error($token_url, $msg['error'], $msg['error_description']);
            return false;
        }
        $this->set_val($msg);

        return true;
    }

    /**
     * callback 之后获取access_token
     * @return mixed
     */
    public function get_login_token()
    {
        return $this->get_val("access_token");
    }

    //https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316518&token=&lang=zh_CN
    //http://www.mamicode.com/info-detail-909227.html
    /**
     *
     * {
     *       "access_token":"ACCESS_TOKEN",
     *       "expires_in":7200,
     *       "refresh_token":"REFRESH_TOKEN",
     *       "openid":"OPENID",
     *       "scope":"SCOPE"
     *  }
     */
    function refresh_token($refresh_token)
    {

        $data = array(//
            'appid' => $this->akey,//
            'refresh_token' => $refresh_token,//
            'grant_type' => "refresh_token",//
        );
        $url = self::URL . 'oauth2/refresh_token';

        $response = $this->get_data($url, $data, false);
        $msg = json_decode($response, true);
        if (isset($msg->error)) {
            $this->set_error($url, $msg['error'], $msg['error_description']);
            return false;
        }
        $this->set_val($msg);
        //https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
    }

    /**
     * 获取用户信息，无值从SESSION中取值
     * @param string $openid
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return mixed 失败返回false
     */
    public function get_user_info($openid = "",$check_res = true)
    {
        if ($openid) {
            $data = array('access_token' => $this->get_token(), 'openid' => $openid,);
        } else {
            $data = array('access_token' => $this->get_login_token(), 'openid' => $this->get_openid(),);
        }

        $url = self::URL . 'userinfo';

        $arr = $this->get_data($url, $data, $check_res);

        /*array(9) {
            ["openid"] => string(28) "oTZCOwvyOC9dhFDJSSgsB4KQz6Vo"
            ["nickname"] => string(12) "æµæµªè¯—äºº"
            ["sex"] => int(1)
            ["language"] => string(5) "zh_CN"
            ["city"] => string(4) "Jian"
            ["province"] => string(7) "Jiangxi"
            ["country"] => string(2) "CN"
            ["headimgurl"] => string(119) "http://wx.qlogo.cn/mmopen/PiajxSqBRaEKia5EYEphUSTOciauP7PJmsDibnovl3weia8J02XphQIWhWHDqqf7TwXAB4yJDL7XKoLvAkvZPZVyjnQ/0"
            ["privilege"] => array(0) {
            }
        }*/
        return $arr;
    }
}