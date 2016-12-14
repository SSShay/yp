<?php

/**
 * 微信公众平台消息管理类基类
 * @package wxapi-base
 */
class MsgBase
{
  /**
   * 以数组的形式保存微信服务器每次发来的请求
   *
   * @var array
   */
  private $request;

  /**
   * 初始化，判断此次请求是否为验证请求，并以数组形式保存
   *
   * @param string $token 验证信息
   * @param boolean $debug 调试模式，默认为关闭
   */
  public function __construct($token)
  {
    if ($this->isValid() && $this->validateSignature($token))
    {
      header('content-type:text');
      exit($_GET['echostr']);
    }

    set_error_handler(array(&$this, 'errorHandler'));
    // 设置错误处理函数，将错误通过文本消息回复显示

    $xml = (array)simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'], 'SimpleXMLElement', LIBXML_NOCDATA);

    $this->request = array_change_key_case($xml, CASE_LOWER);
    // 将数组键名转换为小写，提高健壮性，减少因大小写不同而出现的问题
  }

  /**
   * 判断此次请求是否为验证请求
   *
   * @return boolean
   */
  private function isValid()
  {
    return isset($_GET['echostr']);
  }

  /**
   * 判断验证请求的签名信息是否正确
   *
   * @param  string $token 验证信息
   * @return boolean
   */
  private function validateSignature($token)
  {
    $signature = $_GET['signature'];
    $timestamp = $_GET['timestamp'];
    $nonce = $_GET['nonce'];

    $signatureArray = array($token, $timestamp, $nonce);
    sort($signatureArray, SORT_STRING);
    return sha1(implode($signatureArray)) == $signature;
  }

  /**
   * 获取本次请求中的参数，不区分大小
   *
   * @param  string $param 参数名，默认为无参
   * @return mixed
   */
  protected function getRequest($param = FALSE)
  {
    if ($param === FALSE)
    {
      return $this->request;
    }

    $param = strtolower($param);

    if (isset($this->request[$param]))
    {
      return $this->request[$param];
    }

    return NULL;
  }

  /**
   * 用户关注时触发，用于子类重写
   *
   * @return void
   */
  protected function onSubscribe()
  {
  }

  /**
   * 用户取消关注时触发，用于子类重写
   *
   * @return void
   */
  protected function onUnsubscribe()
  {
  }

  /**
   * 收到文本消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onText()
  {
  }

  /**
   * 收到图片消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onImage()
  {
  }

  /**
   * 收到语音消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onVoice()
  {
  }

  /**
   * 收到地理位置消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onLocation()
  {
  }

  /**
   * 收到链接消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onLink()
  {
  }

  /**
   * 收到未知类型消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onUnknown()
  {
  }

  /**
   * 回复文本消息
   *
   * @param  string $content 消息内容
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseText($content, $funcFlag = 0)
  {
    exit(new TextMsg($this->getRequest('fromusername'), $this->getRequest('tousername'), $content, $funcFlag));
  }

  /**
   * 回复图片消息
   *
   * @param  string $media_id 通过素材管理接口上传多媒体文件，得到的id
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseImage($media_id, $funcFlag = 0)
  {
    exit(new ImageMsg($this->getRequest('fromusername'), $this->getRequest('tousername'), $media_id, $funcFlag));
  }

  /**
   * 回复语音消息
   *
   * @param  string $media_id 通过素材管理接口上传多媒体文件，得到的id
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseVoice($media_id, $funcFlag = 0)
  {
    exit(new VoiceMsg($this->getRequest('fromusername'), $this->getRequest('tousername'), $media_id, $funcFlag));
  }

  /**
   * 回复音乐消息
   *
   * @param  string $title 音乐标题
   * @param  string $description 音乐描述
   * @param  string $musicUrl 音乐链接
   * @param  string $hqMusicUrl 高质量音乐链接，Wi-Fi 环境下优先使用
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseMusic($title, $description, $musicUrl, $hqMusicUrl, $funcFlag = 0)
  {
    exit(new MusicMsg($this->getRequest('fromusername'), $this->getRequest('tousername'), $title, $description, $musicUrl, $hqMusicUrl, $funcFlag));
  }

  /**
   * 回复图文消息
   * @param  array $items 由单条图文消息类型 NewsMsgItem() 组成的数组
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseNews($items, $funcFlag = 0)
  {
    exit(new NewsMsg($this->getRequest('fromusername'), $this->getRequest('tousername'), $items, $funcFlag));
  }

  /**
   * 分析消息类型，并分发给对应的函数
   *
   * @return void
   */
  public function run()
  {
    switch ($this->getRequest('msgtype'))
    {
      case 'event':
        switch ($this->getRequest('event'))
        {
          case 'subscribe':
            $this->onSubscribe();
            break;

          case 'unsubscribe':
            $this->onUnsubscribe();
            break;
        }
        break;

      case 'text':
        $this->onText();
        break;

      case 'image':
        $this->onImage();
        break;

      case 'voice':
        $this->onVoice();
        break;

      case 'location':
        $this->onLocation();
        break;

      case 'link':
        $this->onLink();
        break;

      default:
        $this->onUnknown();
        break;

    }
  }

  /**
   * 自定义的错误处理函数，将 PHP 错误通过文本消息回复显示
   * @param  int $level 错误代码
   * @param  string $msg 错误内容
   * @param  string $file 产生错误的文件
   * @param  int $line 产生错误的行数
   * @return void
   */
  protected function errorHandler($level, $msg, $file, $line)
  {
    if (!WX_MSG_DEBUG) return;

    $error_type = array(//
      // E_ERROR             => 'Error',//
        E_WARNING => '警告',//
      // E_PARSE             => 'Parse Error',//
        E_NOTICE => '通知',//
      // E_CORE_ERROR        => 'Core Error',//
      // E_CORE_WARNING      => 'Core Warning',//
      // E_COMPILE_ERROR     => 'Compile Error',//
      // E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR => '用户错误',//
        E_USER_WARNING => '用户警告',//
        E_USER_NOTICE => '用户通知',//
        E_STRICT => '严格',//
        E_RECOVERABLE_ERROR => '可恢复错误',//
        E_DEPRECATED => '不赞成',//
        E_USER_DEPRECATED => '用户弃用',//
    );

    $template = <<<ERR
PHP 报错啦！

%s: %s
File: %s
Line: %s
ERR;

    $this->responseText(sprintf($template, $error_type[$level], $msg, $file, $line));
  }
}



/**
 * 微信消息的基类
 */
abstract class WxMsg
{

  protected $toUserName;
  protected $fromUserName;
  protected $funcFlag;

  public function __construct($toUserName, $fromUserName, $funcFlag)
  {
    $this->toUserName = $toUserName;
    $this->fromUserName = $fromUserName;
    $this->funcFlag = $funcFlag;
  }

  abstract public function __toString();

}

/**
 * 用于回复的文本消息类型
 */
class TextMsg extends WxMsg
{

  protected $content;

  protected $template = '
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[%s]]></Content>
  <FuncFlag>%s<FuncFlag>
</xml>';

  public function __construct($toUserName, $fromUserName, $content, $funcFlag = 0)
  {
    parent::__construct($toUserName, $fromUserName, $funcFlag);
    $this->content = $content;
  }

  public function __toString()
  {
    return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->content,
        $this->funcFlag
    );
  }
}

/**
 * 用于回复的图片消息类型
 */
class ImageMsg extends WxMsg {

  protected $content;

  protected $template = '
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[image]]></MsgType>
  <Image>
  <MediaId><![CDATA[%s]]></MediaId>
  </Image>
  <FuncFlag>%s<FuncFlag>
</xml>';

  public function __construct($toUserName, $fromUserName, $media_id, $funcFlag = 0) {
    parent::__construct($toUserName, $fromUserName, $funcFlag);
    $this->media_id = $media_id;
  }

  public function __toString() {
    return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->media_id,
        $this->funcFlag
    );
  }
}

/*
 * 用于回复的语音消息类型
 */
class VoiceMsg extends WxMsg {

  protected $content;

  protected $template = '
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[voice]]></MsgType>
  <Voice>
  <MediaId><![CDATA[%s]]></MediaId>
  </Voice>
  <FuncFlag>%s<FuncFlag>
</xml>';

  public function __construct($toUserName, $fromUserName, $mediaId,$funcFlag = 0) {
    parent::__construct($toUserName, $fromUserName, $funcFlag);
    $this->mediaId = $mediaId;
  }

  public function __toString() {
    return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->mediaId,
        $this->funcFlag
    );
  }
}

/**
 * 用于回复的音乐消息类型
 */
class MusicMsg extends WxMsg {

  protected $title;
  protected $description;
  protected $musicUrl;
  protected $hqMusicUrl;

  protected $template = '
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[music]]></MsgType>
  <Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
  </Music>
  <FuncFlag>%s<FuncFlag>
</xml>';

  public function __construct($toUserName, $fromUserName, $title, $description, $musicUrl, $hqMusicUrl, $funcFlag) {
    parent::__construct($toUserName, $fromUserName, $funcFlag);
    $this->title = $title;
    $this->description = $description;
    $this->musicUrl = $musicUrl;
    $this->hqMusicUrl = $hqMusicUrl;
  }

  public function __toString() {
    return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        $this->title,
        $this->description,
        $this->musicUrl,
        $this->hqMusicUrl,
        $this->funcFlag
    );
  }
}

/**
 * 用于回复的图文消息类型
 */
class NewsMsg extends WxMsg {

  protected $items = array();

  protected $template = '
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[news]]></MsgType>
  <ArticleCount>%s</ArticleCount>
  <Articles>
    %s
  </Articles>
  <FuncFlag>%s<FuncFlag>
</xml>';

  public function __construct($toUserName, $fromUserName, $items, $funcFlag) {
    parent::__construct($toUserName, $fromUserName, $funcFlag);
    $this->items = $items;
  }

  public function __toString() {
    return sprintf($this->template,
        $this->toUserName,
        $this->fromUserName,
        time(),
        count($this->items),
        implode($this->items),
        $this->funcFlag
    );
  }
}

/**
 * 单条图文消息类型
 */
class NewsMsgItem
{

  protected $title;
  protected $description;
  protected $picUrl;
  protected $url;

  protected $template = '
<item>
  <Title><![CDATA[%s]]></Title>
  <Description><![CDATA[%s]]></Description>
  <PicUrl><![CDATA[%s]]></PicUrl>
  <Url><![CDATA[%s]]></Url>
</item>';

  public function __construct($title, $description, $picUrl, $url)
  {
    $this->title = $title;
    $this->description = $description;
    $this->picUrl = $picUrl;
    $this->url = $url;
  }

  public function __toString()
  {
    return sprintf($this->template, $this->title, $this->description, $this->picUrl, $this->url);
  }
}