<?php

include_once __DIR__ . "/base/WxBase.class.php";

/**
 * 微信菜单管理类
 * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141013&token=&lang=zh_CN
 */
class MenuHandler extends WxBase
{
    const URL = "https://api.weixin.qq.com/cgi-bin/menu/";
    //菜单管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * 创建普通菜单
     * $mh->create(new \Menu(array(
     * new \MainBtn("微家校",array(
     * new \ViewBtn("微家校官网", "http://tool.weijiaxiao.net/mobile.php?act=channelname=indexweid=126"),
     * new \ViewBtn("APP下载", "http://tx.vshangwu.com/mobile.php?act=moduleid=6318weid=79name=sitedo=detail")
     * )),
     * new \ViewBtn("绑定账号", "http://ltwx.weijiaxiao.net/index.php?s=/Index/index")
     * )
     * ));
     * @param mixed $menu 菜单
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function create($menu,$check_res = false)
    {
        $param = array('access_token' => $this->get_token());

        $res = $this->post_data(self::URL . "create", $param, $menu,$check_res);
        return $res;
    }

    /**
     * 获取所有菜单，包括个性化菜单
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function get($check_res = false)
    {
        $param = array('access_token' => $this->get_token());
        $res = $this->post_data(self::URL . "get", $param, '', $check_res);
        return $res;
    }

    /**
     * 调用此接口会删除默认菜单及全部个性化菜单。
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function delete($check_res = false)
    {
        $param = array('access_token' => $this->get_token());
        $res = $this->post_data(self::URL . "delete", $param, '', $check_res);
        return $res;
    }


    //个性化菜单的创建和删除
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $data
     * @param array $matchrule 筛选条件
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function addconditional($data, $matchrule = array(), $check_res = false)
    {
        $param = array('access_token' => $this->get_token());

        if ($matchrule)
        {
            $data = array(//
                "button" => $data,//
                "matchrule" => $matchrule//
            );
        }
        $res = $this->post_data(self::URL . "addconditional", $param, $data, $check_res);
        //{"menuid":414029882}

        return $res;
    }

    /**
     * 删除个性化菜单
     * @param string $menuid 菜单的id
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function delconditional($menuid, $check_res = false)
    {
        $param = array('access_token' => $this->get_token());
        $menuid = '{"menuid":"' . $menuid . '"}';
        $res = $this->post_data(self::URL . "delconditional", $param, $menuid, $check_res);

        return $res;
    }
}



abstract class WxButton{
    /**
     * 按钮显示的文字
     * @var string
     */
    public $name;

    /**
     * 按钮的类型
     * @var string
     */
    public $type;

    public function __construct($name,$type)
    {
        $this->name = $name;
    }
}

/**
 * 一级菜单按钮
 */
class MainBtn
{
    /**
     * 按钮显示的文字
     * @var string
     */
    public $name;

    /**
     * 二级菜单
     * @var array
     */
    public $sub_button;

    public function __construct($name, $sub_button = array())
    {
        $this->name = $name;
        if (empty($sub_button))
        {
            unset($this->sub_button);
        } else
        {
            $this->sub_button = $sub_button;
        }
    }
}

/**
 * 事件的按钮
 */
class ClickBtn extends WxButton
{
    /**
     * 菜单KEY值，用于消息接口推送，不超过128字节
     * @var string
     */
    public $key;

    public function __construct($name, $key)
    {
        parent::__construct($name, 'click');
        $this->key = $key;
        $this->type = "click";
    }
}

/**
 * 链接的按钮
 */
class ViewBtn extends WxButton
{
    /**
     * 网页链接，用户点击菜单可打开链接，不超过1024字节
     * @var string
     */
    public $url;

    public function __construct($name, $url)
    {
        parent::__construct($name, 'click');
        $this->url = $url;
        $this->type = "view";
    }
}

class Menu
{
    /**
     * 按钮列表
     */
    public $button;

    /**
     *
     * "group_id":103,"sex":"1","country":"中国","province":"广东","city":"广州","client_platform_type":"2","language":"zh_CN"
     * @var
     */
    public $matchrule;

    /**
     * @param array $buttons 按钮数组
     * @param array|array() $matchrule 个性化条件
     */
    public function __construct($buttons, $matchrule = array())
    {
        $this->button = $buttons;
        if (empty($matchrule))
        {
            unset($this->matchrule);
        } else
        {
            $this->matchrule = $matchrule;
        }
    }
}

