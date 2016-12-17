<?php

namespace Home\Controller;

use Common\Model\AreaModel;
use Common\Model\BannerModel;
use Common\Model\ScOrderModel;
use Common\Model\ScOrderProductModel;
use Common\Model\ScProductImgModel;
use Common\Model\ScProductModel;

/**
 * 商城
 * Class MallController
 * @package Home\Controller
 */
class MallController extends BaseController
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

    //首页
    public function index()
    {
        //轮播图
        $banner_list = session('banner_mall');
        if (!isset($banner_list)) {
            $banner_obj = new BannerModel();
            $banner_list = $banner_obj->selectListByHome(BannerModel::T_mall);
            session('banner_mall', $banner_list);
        }
        $this->banner = $banner_list;


        $product_obj = new ScProductModel();
        $product_list = $product_obj->selectListByHome();
        $this->product_list = $product_list;
        $this->display('index');
    }


    //商品详情页
    public function detail()
    {
        $this->id = I('get.id');
        $where['id'] = $this->id;
        $where['display'] = 1;
        $sc_product_obj = new ScProductModel();
        $res = $sc_product_obj->findObj($where, 'name,price,brief,details');
        if ($res) {
            //$res['details'] = addslashes($res['details']);
            $this->product = $res;

            $sc_product_img_obj = new ScProductImgModel();
            $this->img_list = $sc_product_img_obj->selectListByHome($this->id);

            $this->breadcrumb('商品详情');
            $this->display('detail');
        } else {
            $this->error('<pre>No.' . $this->id . '</pre>所对应的商品不存在或已下架！');
        }
    }

    /**
     * 添加到购物车
     *
     * 占时没有区分各种分类，如果以后区分可以用逗号分隔类别id。2016-12-16.xpf
     */
    public function add_to_cart()
    {
        if (IS_POST) {
            $id = intval(I('post.id'));
            $count = intval(I('post.count'));
            $types = I('post.types');       //类别暂时为空、detail页面的btn-buy按钮请求

            if ($id && $count) {
                $cart = session('cart');
                $key = $id;
                if ($types) $key .= '.' . $types;
                $_count = $cart[$key];
                $cart[$key] = $_count ? $_count + $count : $count;

                session('cart', $cart);

                $re['success'] = true;
            } else {
                $re['error'] = '加入购物车失败！';
            }

            echo json_encode($re);
        }
    }

    /**
     * 从购物车移除
     *
     * 占时没有区分各种分类，如果以后区分可以用逗号分隔类别id。2016-12-16.xpf
     */
    public function remove_in_cart()
    {
        $key = I('get.key');
        if ($key) {
            $cart = session('cart');
            if (isset($cart[$key])) {
                unset($_SESSION[C('SESSION_PREFIX')]['cart'][$key]);

                $re['success'] = true;
            } else {
                $re['error'] = '从购物车移除失败！';
            }

            echo json_encode($re);
        }
    }


    const freight = 10;
    /**
     * 确认订单
     */
    public function confirm_order()
    {
        $cart = session('cart');
        $list = array();    //购物清单
        $invalid = array(); //失效列表
        $subtotal = 0;  //小计
        $freight = self::freight;  //运费

        if($cart) {
            $sc_product_obj = new ScProductModel();
            $prefix = C('SESSION_PREFIX');
            foreach ($cart as $k => $n) {
                $param = explode('.', $k);
                $id = $param[0];
                $where['id'] = $id;
                $where['display'] = 1;
                $product = $sc_product_obj->findObj($where, 'name,price,thumb');
                if ($product) {
                    $product['key'] = $k;
                    $product['id'] = $id;
                    $product['count'] = $n;
                    $st = $product['price'] * $n;
                    $product['subtotal'] = sprintf("%.2f", $st);

                    $list[] = $product;
                    $subtotal += $st;
                } else {
                    unset($_SESSION[$prefix]['cart'][$k]);
                    $invalid[] = $id;
                }
            }
        }

        if(isset($_GET['pid']) && $pid = intval($_GET['pid'])) {
            $breadcrumb[] = array('name' => '商品详情', 'url' => U('Mall/detail', array('id' => $pid)));
        }
        $breadcrumb[] = array('name' => '确认订单');
        $this->breadcrumb($breadcrumb);

        $this->list = $list;
        $this->invalid = count($invalid);
        $this->subtotal = sprintf("%.2f", $subtotal);
        $this->freight = sprintf("%.2f", $freight);
        $this->total = sprintf("%.2f", $subtotal + $freight);

        $this->display('confirm_order');
    }

    public function get_area()
    {
        $topid = I('get.topid');
        $level = I('get.level');
        $area_obj = new AreaModel();
        $list = $area_obj->selectArea($topid, $level);

        echo json_encode($list);
    }

    /*
     * 选择付款方式
     */
    public function payment()
    {
        if (IS_POST) {
            $this->create_order();
        } else {
            $oid = I('get.id');
            $this->breadcrumb(array(
                array('name' => '确认订单'),
                array('name' => '选择付款方式')
            ));

            $sc_order_obj = new ScOrderModel();
            $where['id'] = $oid;
            $order = $sc_order_obj->findObj($where, 'total,ctime');
            if ($order) {
                $order['order_id'] = PayController::enpayno($oid, $order['ctime']);
                $order['rest'] = $order['ctime'] + 3600 - NOW_TIME;
                $this->order = $order;
                $this->oid = $oid;

                $this->display('payment');
            } else {
                $this->error('订单不存在！');
            }
        }
    }

    //创建订单
    private function create_order()
    {
        $list = $_POST['list'];
        if (!empty($list) && is_array($list)) {
            $flag = true;                //是否成功的标记
            $sc_order_obj = new ScOrderModel();
            $sc_order_obj->startTrans();
            $oid = $sc_order_obj->addOrder(I('post.addressee'), I('post.mobile'), I('post.area_id'), I('post.addr_detail'));
            if ($oid) {
                $total = self::freight;     //价格总数
                $i = 0;                       //计数
                $describe = '';               //描述
                $cart = session('cart');     //购物车
                $order_list = array();       //购买列表
                $perfix = C('SESSION_PREFIX');

                $sc_product_obj = new ScProductModel();
                $sc_order_product_obj = new ScOrderProductModel();
                foreach ($list as $k => $n) {
                    if (isset($cart[$k])) {
                        unset($_SESSION[$perfix]['cart'][$k]);

                        $param = explode('.', $k);
                        $pid = $param[0];

                        $where['id'] = $pid;
                        $where['display'] = 1;
                        $product = $sc_product_obj->findObj($where, 'name,price');
                        if ($product) {
                            if ($i++ == 0) $describe = $product['name'];

                            $pobj['order_id'] = $oid;
                            $pobj['product_id'] = $pid;
                            $pobj['describe'] = $product['name'];
                            $pobj['price'] = $product['price'];
                            $pobj['count'] = $n;
                            $res = $sc_order_product_obj->addObj($pobj);
                            if ($res) {
                                $total += $product['price'] * $n;
                            } else {
                                $flag = false;
                                $re['error'] = $sc_order_obj->getError();
                                break;
                            }

                            $order_list[$k] = $n;
                        } else {
                            $flag = false;
                            $re['error'] = '部分商品已失效！';
                            $re['refresh'] = 1500;
                            break;
                        }
                    }else{
                        $flag = false;
                        $re['error'] = '部分商品已不在购物车！';
                        $re['refresh'] = 1500;
                    }
                }

                if($flag) {
                    if ($i > 1) $describe .= ' 等' . $i . '件';
                    $res = $sc_order_obj->setTotal($oid, $total, $describe);
                    if ($res) {
                        $re['success'] = $oid;
                    } else {
                        $re['error'] = $sc_order_obj->getError();
                        $flag = false;
                    }
                }
            } else {
                $flag = false;
                $re['error'] = $sc_order_obj->getError();
            }

            if ($flag) {
                $sc_order_obj->commit();
            } else {
                $sc_order_obj->rollback();
                //失败还原购物车
                if(!empty($order_list)){
                    $_cart = session('cart');
                    foreach($order_list as $k => $n){
                        $_cart[$k] = $n;
                    }
                    session('cart', $_cart);
                }
            }
        } else {
            $re['error'] = '提交的商品列表为空！';
        }

        echo json_encode($re);
    }
}