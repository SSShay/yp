<?php

namespace Admin\Controller;
use Common\Model\AreaModel;
use Common\Model\BannerModel;
use Common\Model\ScExpressModel;
use Common\Model\ScOrderModel;
use Common\Model\ScOrderProductModel;
use Common\Model\ScPayModel;
use Common\Model\ScProductImgModel;
use Common\Model\ScProductModel;

/**
 * 商城后台管理
 * Class MallController
 * @package Admin\Controller
 */
class MallController extends BaseController
{

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  轮播图管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    const banner_type = BannerModel::T_mall;

    public function banner()
    {
        $this->U_check_permissions();

        $banner_obj = new BannerModel();
        $banner = $banner_obj->selectList(self::banner_type);
        $this->banner = json_encode($banner);

        $this->display('banner');
    }

    //轮播图上传
    public function banner_upload()
    {
        $this->U_check_permissions('banner');
        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 512000,//最大上传文件大小不超过500K
                'rootPath' => 'Uploads',
                'savePath' => '/banner/',
                'saveName' => array('uniqid'),
                'exts' => array('jpg'),
                'autoSub' => false,
                'subName' => array('date', 'Ymd'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['img'];
                //返回文件地址和名给JS作回调用
                $img = '/Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                $id = I('post.id');
                $banner_obj = new BannerModel();
                if ($id) {
                    $res = $banner_obj->setBanner($id, $img);
                } else {
                    $res = $banner_obj->addBanner($img, I('post.sort'),self::banner_type);
                    if ($res) $result['id'] = $res;
                }
                if ($res) {
                    $result['img'] = $img;
                    $this->clear_banner();
                } else $result['error'] = $banner_obj->getError();
            } else {
                $result['error'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['error'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    //轮播图删除
    public function banner_del()
    {
        $this->U_check_permissions('banner');
        if (IS_AJAX) {
            $banner_obj = new BannerModel();
            $res = $banner_obj->delBanner(I('post.id'));
            if ($res) {
                $r['success'] = true;
                $this->clear_banner();
            } else {
                $r['error'] = $banner_obj->getError();
            }
            echo json_encode($r);
        }
    }

    //轮播图修改
    public function banner_mod()
    {
        $this->U_check_permissions('banner');
        if (IS_POST) {
            $change = I('post.change');
            $banner_obj = new BannerModel();
            $return['fail'] = 0;
            $return['count'] = count($change);
            foreach ($change as $v) {
                $where['id'] = $v['id'];
                $v['utime'] = time();
                unset($v['id']);
                $res = $banner_obj->setObj($where, $v);
                if (!$res) $return['fail']++;
            }

            $this->clear_banner();
            echo json_encode($return);
        }
    }

    private function clear_banner(){
        session('banner_mall', null);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 商品管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const product_page_key = 'product-page-index';

    public function product(){
        $this->U_check_permissions();

        $this->page_index = session(self::product_page_key);
        if(!$this->page_index) $this->page_index = 0;

        $this->display('product');
    }

    public function product_list()
    {
        $this->U_check_permissions('product');

        $sc_product_obj = new ScProductModel();
        $p = I('get.p');
        session(self::product_page_key, $p);
        $list = $sc_product_obj->selectList('', $p);

        echo json_encode($list);
    }

    // 保存商品属性
    public function product_save()
    {
        $this->U_check_permissions('product');
        $sc_product_obj = new ScProductModel();
        $where['id'] = I('post.id');
        unset($_POST['id']);
        if(isset($_POST['thumb'])) {
            $res = $sc_product_obj->findObj($where, 'thumb');
            if ($res) $oldimg = $res['thumb'];
        }
        $res = $sc_product_obj->setObj($where, $_POST);
        if($res){
            $re['success'] = $res;
            if(isset($oldimg))unlink($oldimg);
        }else{
            $re['error'] = $sc_product_obj->getError();
            if(!$re['error']) $re['error'] = '未知错误，请重试！';
        }
        echo json_encode($re);
    }

    //文章缩略图片上传
    public function product_thumb_upload()
    {
        $this->U_check_permissions('product');

        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 102400,//最大上传文件大小不超过100K
                'rootPath' => 'Uploads',
                'savePath' => '/product/thumb/',
                'saveName' => date('YmdHis') . '_' . rand(1000, 9999),
                'autoSub' => false,
                'exts' => array('jpg', 'png'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['img'];
                //返回文件地址和名给JS作回调用
                $img = '/Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                $oldimg = I('post.img');
                if ($oldimg) unlink($oldimg);
                $result['img'] = $img;
            } else {
                $result['error'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['error'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    //商品详情内容图片上传
    //参照 plugin-kindeditor/php/upload_json.php 上传接口修改
    public function product_details_upload()
    {
        $this->U_check_permissions('product');

        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 262144,//最大上传文件大小不超过256K
                'rootPath' => 'Uploads',
                'savePath' => '/product/details/',
                'saveName' => array('uniqid'),
                'exts' => array('jpg', 'png'),
                'subName' => array('date', 'Y-m-d'),
            );
            $result['error'] = 1;
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['imgFile'];
                //返回文件地址和名给JS作回调用
                $img = '/Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                /*$oldimg = I('post.img');
                if ($oldimg) unlink($oldimg);*/
                $result['error'] = 0;
                $result['url'] = $img;
            } else {
                $result['message'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['message'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    //商品预览图上传
    public function product_img_upload()
    {
        $this->U_check_permissions('product');

        $pid = I('post.pid');
        if ($pid) {
            if (!empty($_FILES)) {
                //图片上传设置
                $config = array(
                    'maxSize' => 262144,//最大上传文件大小不超过256K
                    'rootPath' => 'Uploads',
                    'savePath' => '/product/img/',
                    'saveName' => $pid . '_' . date('YmdHis'),
                    'autoSub' => false,
                    'exts' => array('jpg', 'png'),
                );
                $upload = new \Think\Upload($config);// 实例化上传类
                $images = $upload->upload();
                //判断是否有图
                if ($images) {
                    $imginfo = $images['img'];
                    //返回文件地址和名给JS作回调用
                    $img = 'Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                    try {
                        $image = new \Think\Image();
                        $image->open($img);
                        $suffix = substr(strrchr($imginfo['savename'], '.'), 1);
                        $thumb = 'Uploads' . $imginfo['savepath'] . basename($imginfo['savename'], "." . $suffix) . '_0.' . $suffix;
                        $image->thumb(150, 150)->save($thumb);

                        $id = I('post.id');
                        if (!$id) {
                            $sc_product_img_obj = new ScProductImgModel();
                            $res = $sc_product_img_obj->addImg($pid, '/' . $img, '/' . $thumb, I('post.sort'));
                            if ($res) {
                                $result['img'] = '/' . $img;
                                $result['thumb'] = '/' . $thumb;
                                $result['id'] = $res;

                            } else {
                                $result['error'] = '图片信息保存失败！';//缩略图保存失败
                            }
                        } else {
                            $oldimg = I('post.img');
                            if ($oldimg) unlink($oldimg);
                            $result['img'] = '/' . $img;
                            $result['thumb'] = '/' . $thumb;
                        }
                    } catch (\Exception $e) {
                        $result['error'] = '缩略图保存失败！';//缩略图保存失败
                    }
                } else {
                    $result['error'] = $upload->getError();//获取失败信息
                }
            } else {
                $result['error'] = "图片上传失败！";
            }
        } else {
            $result['error'] = "未指定商品ID！";
        }

        echo json_encode($result);
    }

    // 删除商品图片
    public function product_img_mod()
    {
        $this->U_check_permissions('product');
        if (IS_POST) {
            $change = I('post.change');
            $sc_product_img_obj = new ScProductImgModel();
            $return['fail'] = 0;
            $return['count'] = count($change);
            foreach ($change as $v) {
                $where['id'] = $v['id'];
                $v['utime'] = time();
                unset($v['id']);
                $res = $sc_product_img_obj->setObj($where, $v);
                if (!$res) $return['fail']++;
            }

            echo json_encode($return);
        }
    }

    // 删除商品图片
    public function product_img_del()
    {
        $this->U_check_permissions('product');
        if (IS_POST) {
            $pid = I('post.pid');
            $id = I('post.id');
            $sc_product_img_obj = new ScProductImgModel();
            $res = $sc_product_img_obj->delImg($pid, $id);
            if($res){
                $return['success'] = true;
            }else{
                $return['error'] = $sc_product_img_obj->getError();
            }
            echo json_encode($return);
        }
    }

    // 删除商品
    public function product_del()
    {
        $this->U_check_permissions('product');
        $sc_product_obj = new ScProductModel();
        $where['id'] = I('post.id');
        $res = $sc_product_obj->deleteObj($where);
        if($res){
            $re['success'] = $res;
        }else{
            $re['error'] = $sc_product_obj->getError();
        }
        echo json_encode($re);
    }

    public function product_mod()
    {
        $this->U_check_permissions('product');
        $this->id = I('get.id');
        $where['id'] = $this->id;
        $sc_product_obj = new ScProductModel();
        $res = $sc_product_obj->findObj($where);
        if ($res) {
            $res['details'] = addslashes($res['details']);
            $this->product = $res;

            $sc_product_img_obj = new ScProductImgModel();
            $res = $sc_product_img_obj->selectList($this->id);
            $this->img_list = $res ? json_encode($res) : '[]';

            $this->display('product_mod');
        } else {
            $this->error('<pre>No.' . $this->id . '</pre>所对应的商品不存在！');
        }
    }

    public function product_add()
    {
        $this->U_check_permissions('product');
        if (IS_POST) {
            $sc_product_obj = new ScProductModel();
            $id = $sc_product_obj->addObj($_POST);
            if($id){
                $re['success'] = $id;
            }else{
                $re['error'] = $sc_product_obj->getError();
            }
            echo json_encode($re);
        } else {
            $this->display('product_add');
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 订单管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const order_page_key = 'order-page-index';

    //订单列表页面
    public function order(){
        $this->U_check_permissions();

        $this->page_index = session(self::order_page_key);
        if(!$this->page_index) $this->page_index = 0;

        $this->display('order');
    }

    //订单列表加载
    public function order_list()
    {
        $this->U_check_permissions('order');

        $sc_order_obj= new ScOrderModel();
        $p = I('get.p');
        session(self::order_page_key, $p);
        $list = $sc_order_obj->selectList('', $p);

        echo json_encode($list);
    }

    //订单详情
    public function order_detail()
    {
        $this->U_check_permissions('order');

        $orderid = I('get.id');
        $sc_order_obj = new ScOrderModel();
        $where['id'] = $orderid;
        $order = $sc_order_obj->findObj($where, 'addressee,mobile,area_id,addr_detail,total,describe,ctime');
        if ($order) {
            $this->orderid = $orderid;
            $this->order = $order;

            $area_obj = new AreaModel();
            $area = $area_obj->findDetail($order['area_id']);
            $this->area = implode(' ', $area);

            $sc_express_obj = new ScExpressModel();
            $express = $sc_express_obj->findExpress($orderid);
            $this->express = $express;

            $sc_pay_obj = new ScPayModel();
            $pay = $sc_pay_obj->findPay($orderid);
            if ($pay) $pay['type'] = ScPayModel::getType($pay['pay_type']);
            $this->pay = $pay;

            $sc_order_product_obj = new ScOrderProductModel();
            $list = $sc_order_product_obj->selectByOrder($orderid);
            $this->list = $list;

            $this->display('order_detail');
        }

    }

    //订单发货
    public function order_express()
    {

        $order_id = I('post.order_id');
        $number = I('post.express_id');
        $sc_express_obj = new ScExpressModel();
        $res = $sc_express_obj->addExpress($order_id, $number);
        if ($res) {
            $re['success'] = date('Y-m-d H:i:s');
        } else {
            $re['error'] = $sc_express_obj->getError();
        }

        echo json_encode($re);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 优惠券管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function coupon(){
        $this->U_check_permissions();

        $this->display('coupon');
    }
}