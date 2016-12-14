<?php

//微信基本配置
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

return array(

    "akey"=>"",//微信公众账号的唯一凭证，即appid

    "skey"=>"",//微信公众账号的唯一凭证密钥，即appsecret

    "callback_url"=>"http://ltwx.weijiaxiao.net/index.php?s=/Index/callback",//登录授权的回调链接

    "scope"=> "snsapi_login,snsapi_userinfo",//授权的权限

);