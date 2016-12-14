##1.wxapi目录结构
========================================================================================================================
wxapi
│  config.php                   //微信接口常量配置文件，必须在入口引用配置文件
│  JsSDK.class.php              //微信JS-SDK
│  MenuHandler.class.php        //微信菜单管理类
│  UserHandler.class.php        //微信用户管理类
│  TagHandler.class.php         //微信用户标签管理类
│  MsgHandler.class.php         //微信公众平台消息管理演示类
│  
└─base
        MsgBase.class.php       //微信公众平台消息管理类基类
        WxBase.class.php        //微信接口基类【获取access_token】
        wx_config.php           //微信公众号基本配置