<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/19 0019
 * Time: 下午 12:44
 */
class ChuanglanSms
{
    /*
     * 创蓝的参数配置
     */
    private $config;

    public function __construct($config = array())
    {
        $this->config = include "config.php";
    }

    public function __get($key)
    {
        return $this->config[$key];
    }

    /**
     * 发送短信验证码
     * @param $mobile
     * @param $str
     * @return int|string
     */
    public function sendVerify($mobile, $str)
    {
        $code = mt_rand(1000, 9999);

        $res = $this->send($mobile, '验证码：' . $code . '，' . $str . '请即时输入。', 'true');

        return $res ? $code : '';
    }

    /**
     * @param string $mobile 手机号码
     * @param string $msg 短信内容
     * @param string $needstatus 是否需要状态报告
     * @param string $product 产品id，可选
     * @param string $extno 扩展码，可选
     * @return mixed
     */
    private function send($mobile, $msg, $needstatus = 'false', $product = '', $extno = '')
    {
        if(!$this->debug){
            //创蓝接口参数
            $postArr = array(
                'account' => $this->account,
                'pswd' => $this->pwd,
                'msg' => $msg,
                'mobile' => $mobile,
                'needstatus' => $needstatus,
                'product' => $product,
                'extno' => $extno
            );

            $result = $this->post($this->url_send, $postArr);
            return $this->execRes($result);
        }
        return true;
    }

    /**
     * 处理返回值
     *
     */
    private function execRes($result)
    {
        $result = preg_split("/[,\r\n]/", $result);
        if (!isset($result) || !isset($result[1])) return false;
        return $result[1] == 0;
    }

    /**
     * 查询额度
     */
    public function queryBalance()
    {
        $postArr = array(
            'account' => $this->account,
            'pswd' => $this->pwd,
        );
        $result = $this->post($this->url_query, $postArr);
        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    private function post($url, $postFields)
    {
        $postFields = http_build_query($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}