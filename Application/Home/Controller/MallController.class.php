<?php

namespace Home\Controller;

use Common\Model\BannerModel;
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
            $this->img_list = $sc_product_img_obj->selectListByHome();

            $this->breadcrumb('商品详情');
            $this->display('detail');
        } else {
            $this->error('<pre>No.' . $this->id . '</pre>所对应的商品不存在！');
        }
    }

}