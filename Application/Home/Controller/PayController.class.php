<?php

namespace Home\Controller;
use Common\Model\ScOrderModel;

/**
 * 支付模块
 * Class PayController
 * @package Home\Controller
 */
class PayController extends BaseController
{
    protected function _before_display()
    {
        parent::_before_display();

        //设置导航栏的active标记
        $this->indexnav = '官方商城';
    }

    protected function breadcrumb($arr = array())
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                $arr = array(array('name' => $arr));
            } elseif (!is_array($arr[0])) $arr = array($arr);
        }
        $this->breadcrumb = array_merge(
            array(
                array('name' => '首页', 'url' => U('Index/index')),
                array('name' => '商城', 'url' => U('Mall/index')),
            ), $arr);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //支付相关静态方法
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * 拼接订单id
     *
     *
     * 关于订单的规则：
     * PM2016121700000001
     * PM => panyard mall
     * 20161217 => order表ctime的时间
     * 00000001 => order表id（8位空左补0）
     *
     * @param string $orderid order表id（8位空左补0）
     * @param string $ctime order表ctime的时间
     * @return string 拼接订单id
     */
    public static function enpayno($orderid,$ctime)
    {
        return 'PM' . date('Ymd', $ctime) . str_pad($orderid, 8, '0', STR_PAD_LEFT);
    }

    /**
     * 解析订单id
     */
    public static function depayno($payno)
    {

        $order['time'] = substr($payno, 2, 8);
        $order['id'] = intval(substr($payno, 10, 8));

        var_dump($order);
    }

    public function test(){
        self::depayno('PM2016121700000001');
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //支付：支付宝
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
     * 支付
     */
    public function index()
    {
        $type = I('get.type');
        switch ($type) {
            case 1:
                $this->alipay();
                break;

            default:

        }
    }


    private function alipay()
    {
        require_once __API__ . 'alipayapi/alipay_submit.class.php';

        $host = 'http://' . $_SERVER["HTTP_HOST"];
        $alipaySubmit = new \AlipaySubmit(array(
            'notify_url' => $host . U('Pay/alipay_notify'),
            'return_url' => $host . U('Pay/alipay_return')
        ));
        //$payno = self::enpayno();
        $order_id = I('get.oid');
        $order_obj = new ScOrderModel();
        $where['id'] = $order_id;
        $order = $order_obj->findObj($where, 'total,describe,ctime');
        $payno = self::enpayno($order_id, $order['ctime']);
        $html_text = $alipaySubmit->go_pay($payno, $order['describe'], $order['total']);

        echo $html_text;
    }

    /**
     * 支付宝服务器主动通知商户网站里指定的页面
     */
    public function alipay_notify()
    {
        require_once __API__ . 'alipayapi/alipay_notify.class.php';

        $alipayNotify = new \AlipayNotify();
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代


            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];


            if($_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //付款完成后，支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "success";		//请不要修改或删除

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

    /**
     * 支付宝处理完请求后，当前页面自动跳转到商户网站里指定页面
     */
    public function alipay_return()
    {
        require_once __API__ . 'alipayapi/alipay_notify.class.php';

        $alipayNotify = new \AlipayNotify();
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号

            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号

            $trade_no = $_GET['trade_no'];

            //交易状态
            $trade_status = $_GET['trade_status'];


            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
            }
            else {
                echo "trade_status=".$_GET['trade_status'];
            }

            echo "验证成功<br />";

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "验证失败";
        }
    }

}