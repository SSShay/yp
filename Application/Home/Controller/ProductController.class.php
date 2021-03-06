<?php

namespace Home\Controller;

/**
 * 产品介绍
 * Class ProductController
 * @package Home\Controller
 */
class ProductController extends BaseController
{

    protected function _before_display()
    {
        parent::_before_display();

        //设置导航栏的active标记
        $this->indexnav = '产品介绍';
    }

    protected function breadcrumb($arr = array())
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                $arr = array(array('name' => $arr));
            } elseif (!is_array($arr[0])) $arr = array($arr);
        }
        $this->breadcrumb = array_merge(array(array('name' => '产品介绍')), $arr);
    }

    //产品模块：微家校
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function init_wjx_nav()
    {
        $this->childnav(array(
            array('name' => '产品下载', 'url' => U('Product/wjx_download')),
            array('name' => '使用注意', 'url' => U('Product/wjx_faq')),
        ));
    }

    public function weijiaxiao()
    {
        $this->init_wjx_nav();
        $this->breadcrumb(array('name' => '微家校'));
        $this->page_nofoot('weijiaxiao');
    }

    public function wjx_download()
    {
        $this->init_wjx_nav();
        $this->breadcrumb(array(array('name' => '微家校', 'url' => U('Product/weijiaxiao')), array('name' => '产品下载')));
        $this->page_full('wjx_download');
    }

    public function wjx_faq()
    {
        $this->init_wjx_nav();
        $this->breadcrumb(array(array('name' => '微家校', 'url' => U('Product/weijiaxiao')), array('name' => '使用注意')));
        $this->display('wjx_faq');
    }

    //产品模块：安心星
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function init_axx_nav()
    {
        $this->childnav(array(
            //array('name' => '产品参数','url' => U('Product/axx_parameter')),
            //array('name' => '使用注意','url' => U('Product/axx_faq')),
        ));
    }

    public function anxinxing()
    {
        $this->init_axx_nav();
        $this->breadcrumb(array('name' => '安心星'));
        $this->page_nofoot('anxinxing');
    }

    /*public function axx_parameter()
    {
        $this->init_axx_nav();
        $this->breadcrumb(array(array('name' => '安心星', 'url' => U('Product/anxinxing')), array('name' => '产品参数')));
        $this->page_full('axx_parameter');
    }*/

    public function axx_faq()
    {
        $this->init_axx_nav();
        $this->breadcrumb(array(array('name' => '安心星', 'url' => U('Product/anxinxing')), array('name' => '使用注意')));
        $this->display('axx_faq');
    }


    //产品模块：MDM
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function mdm()
    {
        $this->breadcrumb(array('name' => 'MDM'));
        $this->page_nofoot('mdm');
    }

    //产品模块：教学移动答题卡
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function datika()
    {
        $this->breadcrumb(array('name' => '互动答题卡'));
        $this->page_nofoot('datika');
    }

    //产品模块：录播系统
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function video_sys()
    {
        $this->breadcrumb(array('name' => '录播系统'));
        $this->page_nofoot('video_sys');
    }

    //产品模块：校园一卡通
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function campus_card()
    {
        $this->breadcrumb(array('name' => '校园一卡通'));
        $this->page_nofoot('campus_card');
    }
}