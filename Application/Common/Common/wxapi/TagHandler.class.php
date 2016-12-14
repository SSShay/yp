<?php

include_once __DIR__ . "/base/WxBase.class.php";

/**
 * 微信用户标签管理类
 * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140837&token=&lang=zh_CN
 */
class TagHandler extends WxBase
{
    const URL = 'https://api.weixin.qq.com/cgi-bin/tags/';

    /**
     * 创建标签
     * @param string $tag_name 标签名
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function create($tag_name,$check_res = false)
    {
        $param = array('access_token' => $this->get_token());
        $data = array("tag" => array("name" => $tag_name));

        $res = $this->post_data(self::URL . "create", $param, $data, $check_res);
        return $res;
    }

    /**
     * 获取公众号已创建的标签
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function get($check_res = false)
    {
        $param = array('access_token' => $this->get_token());

        $res = $this->post_data(self::URL . "get", $param, "", $check_res);
        return $res;
    }

    /**
     * 编辑标签
     * @param string|array $tag json字符串或者数组或者标签id
     * @param string $tag_name 标签名
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return string|array
     */
    public function update($tag,$tag_name="",$check_res = false)
    {
        $param = array('access_token' => $this->get_token());

        if (is_array($tag)) $tag = array('tag' => $tag); //
        elseif ($tag_name) $tag = array('tag' => array('id' => $tag, 'name' => $tag_name));

        $res = $this->post_data(self::URL . "update", $param, $tag, $check_res);
        return $res;
    }

    /**
     * 删除标签
     * @param int $tag_id 标签id
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return bool
     */
    public function delete($tag_id,$check_res = false)
    {
        $param = array('access_token' => $this->get_token());
        $data = array('tag' => array('id' => $tag_id));

        $res = $this->post_data(self::URL . "delete", $param, $data, $check_res);
        return $res;
    }

    /**
     * 批量为用户绑定标签
     * @param array|string $openid_list openid列表 可以是单个openid
     * @param string $tag_id 标签id
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return bool
     */
    public function bind($openid_list,$tag_id = "",$check_res = true)
    {
        $param = array('access_token' => $this->get_token());
        if ($tag_id) $openid_list = array(//
            'openid_list' => is_array($openid_list)?$openid_list: array($openid_list),//
            'tagid' => $tag_id
        );

        $res = $this->post_data(self::URL . "members/batchtagging", $param, $openid_list, $check_res);
        return $res;
    }

    /**
     * 批量为用户解除标签绑定
     * @param array|string $openid_list openid列表 可以是单个openid
     * @param string $tag_id 标签id
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return bool
     */
    public function unbind($openid_list,$tag_id = "",$check_res = true)
    {
        $param = array('access_token' => $this->get_token());
        if ($tag_id) $openid_list = array(//
            'openid_list' => is_array($openid_list)?$openid_list: array($openid_list),//
            'tagid' => $tag_id
        );

        $res = $this->post_data(self::URL . "members/batchuntagging", $param, $openid_list, $check_res);
        return $res;
    }

    /**
     * 批量为用户解除标签绑定
     * @param array|string $openid openid列表 可以是单个openid
     * @param bool|false $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return bool
     */
    public function getidlist($openid,$check_res = true)
    {
        $param = array('access_token' => $this->get_token());

        $data = array('openid' => $openid);

        $res = $this->post_data(self::URL . "members/batchuntagging", $param, $data, $check_res);
        return $res;
    }
}