<?php

namespace Home\Controller;

use Common\Model\BannerModel;
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

}