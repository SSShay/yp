<?php
/* *
 * 类名：AlipaySubmit
 * 功能：支付宝各接口请求提交类
 * 详细：构造支付宝各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
require_once("lib/alipay_core.function.php");
require_once("lib/alipay_md5.function.php");

class AlipaySubmit
{

	var $alipay_config;
	/**
	 *支付宝网关地址（新）
	 */
	var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

	function __construct($alipay_config = array())
	{
		$_alipay_config = include 'alipay.config.php';
		foreach ($alipay_config as $k => $v) {
			$_alipay_config[$k] = $v;
		}
		$this->alipay_config = $_alipay_config;
	}

	function AlipaySubmit($alipay_config)
	{
		$this->__construct($alipay_config);
	}

	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort)
	{
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);

		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$mysign = md5Sign($prestr, $this->alipay_config['key']);
				break;
			default :
				$mysign = "";
		}

		return $mysign;
	}

	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	function buildRequestPara($para_temp)
	{
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);

		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));

		return $para_sort;
	}

	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组字符串
	 */
	function buildRequestParaToString($para_temp)
	{
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);

		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = createLinkstringUrlencode($para);

		return $request_data;
	}

	/**
	 * 建立请求，以表单HTML形式构造（默认）
	 * @param $para_temp 请求参数数组
	 * @param $method 提交方式。两个值可选：post、get
	 * @param $button_name 确认按钮显示文字
	 * @return 提交表单HTML文本
	 */
	function buildRequestForm($para_temp, $method, $button_name)
	{
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);

		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=" . trim(strtolower($this->alipay_config['input_charset'])) . "' method='" . $method . "'>";
		while (list ($key, $val) = each($para)) {
			$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
		}

		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml . "<input type='submit'  value='" . $button_name . "' style='display:none;'></form>";

		$sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

		return $sHtml;
	}


	/**
	 * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
	 * return 时间戳字符串
	 */
	function query_timestamp()
	{
		$url = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim(strtolower($this->alipay_config['partner'])) . "&_input_charset=" . trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

		return $encrypt_key;
	}

	/**
	 * 即时到账交易接口
	 * 2016-12-17.xpf
	 *
	 * @param string $out_trade_no 商户订单号 商户网站订单系统中唯一订单号，必填
	 * @param string $subject 订单名称 必填
	 * @param string $total_fee 付款金额 必填
	 * @param  string $body 订单描述
	 * @param string $show_url 商品展示地址 需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
	 * @return string 提交表单HTML文本
	 */
	public function go_pay($out_trade_no, $subject, $total_fee, $body = '',$show_url = '')
	{
		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service" => $this->alipay_config['service'],
			"partner" => $this->alipay_config['partner'],
			"seller_id" => $this->alipay_config['seller_id'],
			"payment_type" => $this->alipay_config['payment_type'],
			"notify_url" => $this->alipay_config['notify_url'],
			"return_url" => $this->alipay_config['return_url'],

			"anti_phishing_key" => $this->alipay_config['anti_phishing_key'],
			"exter_invoke_ip" => $this->alipay_config['exter_invoke_ip'],
			"out_trade_no" => $out_trade_no,
			"subject" => $subject,
			"total_fee" => $total_fee,
			"body" => $body,
			"show_url" => $show_url,
			"_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
			//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
			//如"参数名"=>"参数值"

		);


		var_dump($parameter);
		exit;

		//建立请求
		$html_text = $this->buildRequestForm($parameter, "get", "确认");
		return $html_text;
	}
}
?>