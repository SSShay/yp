<?php

header("Content-Type:text/html; charset=utf-8");

/**
 * 微信接口基础支持
 */
class WxBase
{
    /**
     * 数据请求配置
     */
    const WX_GET_TIMES = 3;//定义发送get的最大请求次数
    const WX_GET_TIME_OUT = 1;//定义发送get的请求的超时时间，单位：秒
    const WX_POST_TIME_OUT = 10;//定义发送post的请求的超时时间，单位：秒
    const URL = 'https://api.weixin.qq.com/cgi-bin/';

    /*
     * 微信的参数配置
     */
    private $config;

    /*
     * 错误参数
     */
    public $error_url;  //错误出现之前的请求地址
    public $error_id;   //错误编号
    public $error;      //错误信息

    /**
     * 初始化微信配置。数组格式在 wx_config.php 中
     * @param array $config 配置
     */
    public function __construct($config = array())
    {
        if (!isset($_SESSION)) session_start();
        $this->config = array_merge(include "wx_config.php", $config);
    }

    public function __get($key)
    {
        return $this->config[$key];
    }

    /**
     * @param mixed $param1 将值储存在SESSION中
     * @param mixed $param2 键值
     * set_val('a.b.c','d') 等效于 $_SESSION['key']['a']['b']['c'] = “d”
     * set_val(
     *      'a.b'=>'c',
     *      'd.f'=>'e'
     * ) 等效于 $_SESSION['key']['a']['b'] = “c”;$_SESSION['key']['d']['e'] = “f”
     */
    protected function set_val($param1, $param2 = null)
    {
        if (is_array($param1)) {
            foreach ($param1 as $k => $v) {
                $this->set_val($k, $v);
            }
        } else {
            $val = &$_SESSION[WX_KEY];
            $key = explode('.', $param1);
            for ($i = 0, $n = count($key); $i < $n; $i++) {
                $k = $key[$i];
                $val = &$val[$k];
            }
            $val = (is_array($val) && is_array($param2)) ? array_merge($val, $param2) : $param2;
            unset($val);
        }
    }

    /**
     * @param string $key 从SESSION中取出相应的值
     * get('a.b.c','d')取值 等效于 $_SESSION['key']['a']['b']['c'];
     *
     * @return mixed
     */
    public function get_val($key)
    {
        if (!is_array($key)) {
            $key = explode('.', $key);
        }

        if (!array_key_exists(WX_KEY, $_SESSION)) {
            $this->set_error(null, __CLASS__ . ' -> ' . __FUNCTION__, "未找到相应数据：WX_KEY");
            return null;
        }
        $data = $_SESSION[WX_KEY];
        for ($i = 0, $n = count($key); $i < $n; $i++) {
            $k = $key[$i];
            if (!array_key_exists($k, $data)) {
                $this->set_error(null, __CLASS__ . ' -> ' . __FUNCTION__, "未找到相应数据：{$k}");
                return null;
            }
            $data = $data[$k];
        }
        return $data;
    }

    /**
     * @param string $url 请求链接
     * @param string $id 错误的编号
     * @param string $description 错误的描述
     */
    protected function set_error($url, $id, $description = '')
    {
        if (!$description && isset(self::$errors[$id])) $description = self::$errors[$id];
        if ($url !== null) $this->error_url = $url;
        $this->error_id = $id;
        $this->error = $description;

        if (WX_ERROR_LOG) {
            $fp = fopen(WX_LOG_PATH, "a");
            if ($fp) {
                $log = date("Y/m/d H:i:s") . "{$_SERVER['REMOTE_ADDR']}\n请求路径：{$_SERVER["QUERY_STRING"]}\n请求COOKIE：{$_SERVER["HTTP_COOKIE"]}\n请求接口：$url\n错误编号：$id\n错误描述：$description\n\n";
                fwrite($fp, $log);
                fclose($fp);
            }
        }

        if (WX_DEBUG) {
            echo "<span>请求地址:</sp>$url<br>";
            echo "<span>错误编号:</sp>$id<br>";
            echo "<span>错误描述:</span>$description<br>";
            exit;
        }
    }

    /**
     * 发送get请求
     * @param string $url 抓取数据的url链接
     * @param array $param 抓取数据的链接参数
     * @param bool|true $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return array
     */
    protected function get_data($url, $param = array(), $check_res = true)
    {
        $_url = $url . "?" . http_build_query($param);
        $opts = array('http' => array(//
            'method' => "GET",//
            'timeout' => self::WX_GET_TIME_OUT,//单位秒
        ));
        $context = stream_context_create($opts);
        $json = false;
        $i = 0;

        while ($i < self::WX_GET_TIMES && ($json = file_get_contents($_url, false, $context)) === false) $i++;
        if (!$check_res) return $json;
        if ($json === false) {
            $this->set_error($url, "-1");
            return false;
        }
        $result = json_decode($json, true);
        if (isset($result['errcode'])) {
            if ($result['errcode'] === 0) return true;
            $this->set_error($url, $result['errcode']);
            return false;
        }
        return $result;
    }

    /**
     * 发送post请求
     * @param string $url 抓取数据的url链接
     * @param array $param 抓取数据的链接参数
     * @param mixed $data 需要post的数据
     * @param bool|true $check_res 是：验证错误返回数组，否：原样返回json字符串
     * @return array
     */
    protected function post_data($url, $param = array(), $data = "", $check_res = true)
    {
        $opts = array('http' => //
            array('timeout' => self::WX_POST_TIME_OUT,//
                'method' => 'POST',//
                'header' => 'Content-type: application/x-www-form-urlencoded',//
                'content' => is_string($data) ? $data : $this->json_encode($data)//
            ));

        $context = stream_context_create($opts);
        $json = file_get_contents($url . "?" . http_build_query($param), false, $context);
        if (!$check_res) return $json;
        if ($json === false) {
            $this->set_error($url, -1);
            return false;
        }
        $result = json_decode($json, true);
        if (isset($result['errcode'])) {
            if ($result['errcode'] === 0) return true;
            $this->set_error($url, $result['errcode']);
            return false;
        }
        return $result;
    }

    /*
     * 将数组转换为JSON字符串（兼容中文）
     * php5.4之后可以直接通过json_encode转换
     * @param  array $array 要转换的数组
     * @return string      转换得到的json字符串
     * @access public
     */
    public function json_encode($array)
    {
        $str = str_replace("\\/", "/", json_encode($array));
        $search = "#\\\u([0-9a-f]+)#ie";
        if (strpos(strtoupper(PHP_OS), 'WIN') === false) {
            $replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
        } else {
            $replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
        }
        return preg_replace($search, $replace, $str);
    }

    /**
     * 获取公众号的全局唯一票据access_token
     * @return mixed
     */
    protected function get_token()
    {
        //如果不是调试模式关闭无限获取token，改用从seesion中获取
        //token的有效时间 expires_in 为7200秒。
        //管理多个微信公众号时，建议存入数据库，
        //多方操作会使前一方token无效，限制多方登录管理账号
        $url = self::URL . "token";
        if (!WX_DEBUG) {
            $res = wx_token_get($this->akey);
            if (is_array($res)) {
                if ($res['error'] === true) {
                    $this->set_error($url, __CLASS__ . ' -> ' . __FUNCTION__, "不合法操作");
                    return false;
                }
            } else return $res;
        }

        $param = array('grant_type' => 'client_credential', 'appid' => $this->akey, 'secret' => $this->skey);

        $res = $this->get_data($url, $param);
        if (!$res) {
            $this->set_val('token');
            return false;
        }
        $access_token = $res['access_token'];
        wx_token_set($this->akey, $access_token);

        return $access_token;
    }

    /**
     * callback 之后获取openid
     * @return mixed
     */
    public function get_openid()
    {
        return $this->get_val("openid");
    }

    /**
     * 错误列表
     * @var array
     */
    public static $errors = array(//
        '-1' => '系统繁忙，此时请稍候再试',//
        '0' => '请求成功',//
        '40001' => '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口', '40002' => '不合法的凭证类型',//
        '40003' => '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID',//
        '40004' => '不合法的媒体文件类型',//
        '40005' => '不合法的文件类型',//
        '40006' => '不合法的文件大小',//
        '40007' => '不合法的媒体文件id',//
        '40008' => '不合法的消息类型',//
        '40009' => '不合法的图片文件大小',//
        '40010' => '不合法的语音文件大小',//
        '40011' => '不合法的视频文件大小',//
        '40012' => '不合法的缩略图文件大小',//
        '40013' => '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写',//
        '40014' => '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口',//
        '40015' => '不合法的菜单类型',//
        '40016' => '不合法的按钮个数',//
        '40017' => '不合法的按钮个数',//
        '40018' => '不合法的按钮名字长度',//
        '40019' => '不合法的按钮KEY长度',//
        '40020' => '不合法的按钮URL长度',//
        '40021' => '不合法的菜单版本号',//
        '40022' => '不合法的子菜单级数',//
        '40023' => '不合法的子菜单按钮个数',//
        '40024' => '不合法的子菜单按钮类型',//
        '40025' => '不合法的子菜单按钮名字长度',//
        '40026' => '不合法的子菜单按钮KEY长度',//
        '40027' => '不合法的子菜单按钮URL长度',//
        '40028' => '不合法的自定义菜单使用用户',//
        '40029' => '不合法的oauth_code',//
        '40030' => '不合法的refresh_token',//
        '40031' => '不合法的openid列表',//
        '40032' => '不合法的openid列表长度',//
        '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',//
        '40035' => '不合法的参数',//
        '40038' => '不合法的请求格式',//
        '40039' => '不合法的URL长度',//
        '40050' => '不合法的分组id',//
        '40051' => '分组名字不合法',//
        '40117' => '分组名字不合法',//
        '40118' => 'media_id大小不合法',//
        '40119' => 'button类型错误',//
        '40120' => 'button类型错误',//
        '40121' => '不合法的media_id类型',//
        '40132' => '微信号不合法',//
        '40137' => '不支持的图片格式',//
        '41001' => '缺少access_token参数',//
        '41002' => '缺少appid参数',//
        '41003' => '缺少refresh_token参数',//
        '41004' => '缺少secret参数',//
        '41005' => '缺少多媒体文件数据',//
        '41006' => '缺少media_id参数',//
        '41007' => '缺少子菜单数据',//
        '41008' => '缺少oauth code',//
        '41009' => '缺少openid',//
        '42001' => 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明',//
        '42002' => 'refresh_token超时',//
        '42003' => 'oauth_code超时',//
        '42007' => '用户修改微信密码，accesstoken和refreshtoken失效，需要重新授权',//
        '43001' => '需要GET请求',//
        '43002' => '需要POST请求',//
        '43003' => '需要HTTPS请求',//
        '43004' => '需要接收者关注',//
        '43005' => '需要好友关系',//
        '44001' => '多媒体文件为空',//
        '44002' => 'POST的数据包为空',//
        '44003' => '图文消息内容为空',//
        '44004' => '文本消息内容为空',//
        '45001' => '多媒体文件大小超过限制',//
        '45002' => '消息内容超过限制',//
        '45003' => '标题字段超过限制',//
        '45004' => '描述字段超过限制',//
        '45005' => '链接字段超过限制',//
        '45006' => '图片链接字段超过限制',//
        '45007' => '语音播放时间超过限制',//
        '45008' => '图文消息超过限制',//
        '45009' => '接口调用超过限制',//
        '45010' => '创建菜单个数超过限制',//
        '45015' => '回复时间超过限制',//
        '45016' => '系统分组，不允许修改',//
        '45017' => '分组名字过长',//
        '45018' => '分组数量超过上限',//
        '45047' => '客服接口下行条数超过上限',//
        '45056' => '创建的标签数过多，请注意不能超过100个',//
        '45058' => '不能修改0/1/2这三个系统默认保留的标签',//
        '45157' => '标签名非法，请注意不能和其他标签重名',//
        '45158' => '标签名长度超过30个字节',//
        '46001' => '不存在媒体数据',//
        '46002' => '不存在的菜单版本',//
        '46003' => '不存在的菜单数据',//
        '46004' => '不存在的用户',//
        '47001' => '解析JSON/XML内容错误',//
        '48001' => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限',//
        '48004' => 'api接口被封禁，请登录mp.weixin.qq.com查看详情',//
        '48005' => 'api禁止删除被自动回复和自定义菜单引用的素材',//
        '48006' => 'api禁止清零调用次数，因为清零次数达到上限',//
        '50001' => '用户未授权该api',//
        '50002' => '用户受限，可能是违规后接口被封禁',//
        '61451' => '参数错误(invalid parameter)',//
        '61452' => '无效客服账号(invalid kf_account)',//
        '61453' => '客服帐号已存在(kf_account exsited)',//
        '61454' => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)',//
        '61455' => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)',//
        '61456' => '客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)',//
        '61457' => '无效头像文件类型(invalid file type)',//
        '61450' => '系统错误(system error)',//
        '61500' => '日期格式错误',//
        '65301' => '不存在此menuid对应的个性化菜单',//
        '65302' => '没有相应的用户',//
        '65303' => '没有默认菜单，不能创建个性化菜单',//
        '65304' => 'MatchRule信息为空',//
        '65305' => '个性化菜单数量受限',//
        '65306' => '不支持个性化菜单的帐号',//
        '65307' => '个性化菜单信息为空',//
        '65308' => '包含没有响应类型的button',//
        '65309' => '个性化菜单开关处于关闭状态',//
        '65310' => '填写了省份或城市信息，国家信息不能为空',//
        '65311' => '填写了城市信息，省份信息不能为空',//
        '65312' => '不合法的国家信息',//
        '65313' => '不合法的省份信息',//
        '65314' => '不合法的城市信息',//
        '65316' => '该公众号的菜单设置了过多的域名外跳（最多跳转到3个域名的链接）',//
        '65317' => '不合法的URL',//
        '9001001' => 'POST数据参数不合法',//
        '9001002' => '远端服务不可用',//
        '9001003' => 'Ticket不合法',//
        '9001004' => '获取摇周边用户信息失败',//
        '9001005' => '获取商户信息失败',//
        '9001006' => '获取OpenID失败',//
        '9001007' => '上传文件缺失',//
        '9001008' => '上传素材的文件类型不合法',//
        '9001009' => '上传素材的文件尺寸不合法',//
        '9001010' => '上传失败',//
        '9001020' => '帐号不合法',//
        '9001021' => '已有设备激活率低于50%，不能新增设备',//
        '9001022' => '设备申请数不合法，必须为大于0的数字',//
        '9001023' => '已存在审核中的设备ID申请',//
        '9001024' => '一次查询设备ID数量不能超过50',//
        '9001025' => '设备ID不合法',//
        '9001026' => '页面ID不合法',//
        '9001027' => '页面参数不合法',//
        '9001028' => '一次删除页面ID数量不能超过10',//
        '9001029' => '页面已应用在设备中，请先解除应用关系再删除',//
        '9001030' => '一次查询页面ID数量不能超过50',//
        '9001031' => '时间区间不合法',//
        '9001032' => '保存设备与页面的绑定关系参数错误',//
        '9001033' => '门店ID不合法',//
        '9001034' => '设备备注信息过长',//
        '9001035' => '设备申请参数不合法',//
        '9001036' => '查询起始值begin不合法',//
    );
}