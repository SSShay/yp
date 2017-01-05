<?php

namespace Home\Controller;
use Common\Model\ScOrderModel;
use Common\Model\ScPayModel;

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
    public static function depayno($payno,$findorder = '*')
    {
        $order['time'] = substr($payno, 2, 8);
        $order['id'] = intval(substr($payno, 10, 8));

        if ($findorder === false) return $order;

        $sc_order_id = new ScOrderModel();
        $where['id'] = $order['id'];
        $order = $sc_order_id->findObj($where, $findorder);
        return $order;
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
            case ScPayModel::PAY_ali:
                $this->alipay();
                break;

            default:
        }
    }

    private function alipay()
    {
        $order_id = I('get.oid');

        $order_obj = new ScOrderModel();
        $where['id'] = $order_id;
        $order = $order_obj->findObj($where, 'total,describe,ctime');
        if($order) {

            $re['order_id'] = self::enpayno($order_id, $order['ctime']);;
            $re['total'] = $order['total'];

            $sc_pay_obj = new ScPayModel();
            unset($where);
            $where['order_id'] = $order_id;
            $pay = $sc_pay_obj->findObj($where, 'pay_type,status,paytime');

            if ($pay) {
                if ($pay['status'] == ScPayModel::T_pay_success || $pay['status'] == ScPayModel::T_pay_completion) {
                    $re['success'] = true;
                    $re['info'] = '您已经于 ' . date('Y年m月d日 H时i分s秒', $pay['paytime']) . ' 支付过了此订单！';
                } elseif ($pay['status'] == ScPayModel::T_paying) {
                    $re['info'] = '此订单正在退款处理中！';
                } elseif ($pay['status'] == ScPayModel::T_refund_success) {
                    $re['info'] = '此订单已退款成功！';
                } else {
                    $re['error'] = '订单未支付！';
                }
            }elseif (NOW_TIME > $order['ctime'] + ScOrderModel::active_time) {
                $re['error'] = '交易关闭！订单已过期！';
            } else {
                //原因就是所有的参数列表中不能有乱码，所以把这个页面设置下字符集即可！
                header('Content-type:text/html;charset=utf-8');

                require_once __API__ . 'alipayapi/alipay_submit.class.php';

                 $host = 'http://' . $_SERVER["HTTP_HOST"];
                 $alipaySubmit = new \AlipaySubmit(array(
                     'notify_url' => $host . U('Pay/alipay_notify'),
                     'return_url' => $host . U('Pay/alipay_return')
                 ));

                 $payno = self::enpayno($order_id, $order['ctime']);
                 $html_text = $alipaySubmit->go_pay($payno, $order['describe'], $order['total']);

                 echo $html_text;

                //模拟支付
                //$this->testpay();

                exit;
            }

            $this->msg = $re;

            $this->breadcrumb('支付结果');
            $this->display('result');

        }else{
            $this->error('订单不存在！');
        }
    }

    private function testpay(){

        $order_id = I('get.oid');
        $order_obj = new ScOrderModel();
        $where['id'] = $order_id;
        $order = $order_obj->findObj($where, 'total,describe,ctime');
        $out_trade_no = self::enpayno($order_id, $order['ctime']);

        $msg['order_id'] = $out_trade_no;
        $msg['total'] = $order['total'];
        $msg['payment'] = '支付宝';
        //支付宝交易号
        $trade_no = '123456';
        $order = $this->depayno($out_trade_no, 'id');
        if ($order) {
            $sc_pay_obj = new ScPayModel();
            $where['order_id'] = $order['id'];
            $pay = $sc_pay_obj->findObj($where, 'status');
            if ($pay) {
                if ($pay['status'] != ScPayModel::T_pay_success && $pay['status'] != ScPayModel::T_pay_completion) {
                    $data['status'] = ScPayModel::T_pay_success;
                    $res = $sc_pay_obj->setObj($where, $data);

                    if (!$res){
                        $msg['error'] = '支付状态修改失败！';
                    }else{
                        $msg['success'] = true;
                    }
                }else{
                    $msg['success'] = true;
                }
            } else {
                $res = $sc_pay_obj->addPay($order['id'], ScPayModel::PAY_ali, $trade_no);

                if (!$res){
                    $msg['error'] = '订单支付失败，如已扣款，请联系客服处理！';
                }else{
                    $msg['success'] = true;
                }
            }
        } else{
            $msg['error'] = '订单不存在，如已扣款，请联系客服处理！';
        }

        $this->msg = $msg;

        $this->breadcrumb('支付结果');
        $this->display('result');
    }

    /**
     * 支付宝服务器主动通知商户网站里指定的页面
     */
    public function alipay_notify()
    {
        require_once __API__ . 'alipayapi/alipay_notify.class.php';

        $alipayNotify = new \AlipayNotify();
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            write_log('支付回调结果',$_POST);

            if ($trade_status == 'TRADE_FINISHED') {
                $order = $this->depayno($out_trade_no, false);
                if ($order) {
                    $sc_pay_obj = new ScPayModel();
                    $res = $sc_pay_obj->setCompletion($order['id']);

                    if (!$res) exit("fail");
                }
            } else if ($trade_status == 'TRADE_SUCCESS') {
                //支付宝交易号
                $trade_no = $_POST['buyer_email'];
                $order = $this->depayno($out_trade_no, 'id');
                if ($order) {
                    $sc_pay_obj = new ScPayModel();
                    $where['order_id'] = $order['id'];
                    $pay = $sc_pay_obj->findObj($where, 'status');
                    if ($pay) {
                        if ($pay['status'] != ScPayModel::T_pay_success && $pay['status'] != ScPayModel::T_pay_completion) {
                            $data['status'] = ScPayModel::T_pay_success;
                            $data['callback_time'] = NOW_TIME;
                            $res = $sc_pay_obj->setObj($where, $data);

                            if (!$res) exit("fail");
                        }
                    } else {
                        $res = $sc_pay_obj->addPay($order['id'], ScPayModel::PAY_ali, $trade_no, true);

                        if (!$res) exit("fail");
                    }
                } else exit("fail");
            }

            echo "success";
        } else {
            echo "fail";
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
        if ($verify_result) {//验证成功

            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];
            $msg['order_id'] = $out_trade_no;
            $msg['total'] = $_GET['total_fee'];
            $msg['payment'] = '支付宝';

            //交易状态
            $trade_status = $_GET['trade_status'];
            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                //支付宝交易号
                $trade_no = $_GET['buyer_email'];
                $order = $this->depayno($out_trade_no, 'id');
                if ($order) {
                    $sc_pay_obj = new ScPayModel();
                    $where['order_id'] = $order['id'];
                    $pay = $sc_pay_obj->findObj($where, 'status');
                    if ($pay) {
                        if ($pay['status'] != ScPayModel::T_pay_success && $pay['status'] != ScPayModel::T_pay_completion) {
                            $data['status'] = ScPayModel::T_pay_success;
                            $res = $sc_pay_obj->setObj($where, $data);

                            if (!$res) {
                                $msg['error'] = '支付状态修改失败！';
                            } else {
                                $msg['success'] = true;
                            }
                        } else {
                            $msg['success'] = true;
                        }
                    } else {
                        $res = $sc_pay_obj->addPay($order['id'], ScPayModel::PAY_ali, $trade_no);

                        if (!$res) {
                            $msg['error'] = '订单支付失败，如已扣款，请联系客服处理！';
                        } else {
                            $msg['success'] = true;
                        }
                    }
                } else {
                    $msg['error'] = '订单不存在，如已扣款，请联系客服处理！';
                }
            } else {
                $msg['error'] = '订单支付失败，如已扣款，请联系客服处理！';
            }

            $this->msg = $msg;
            $this->breadcrumb('支付结果');

            $this->display('result');
        } else {
            if (isset($_GET['out_trade_no'])) {
                $order = $this->depayno($_GET['out_trade_no'], false);
                $this->redirect('Pay/index', array('oid' => $order['id'], 'type' => ScPayModel::PAY_ali));
            }

            $this->redirect('Mall/index');
        }
    }

}