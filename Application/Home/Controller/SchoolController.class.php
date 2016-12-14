<?php

namespace Home\Controller;

/**
 * 产品介绍
 * Class ProductController
 * @package Home\Controller
 */
class SchoolController extends BaseController
{

    protected function breadcrumb($arr = array())
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                $arr = array(array('name' => $arr));
            } elseif (!is_array($arr[0])) $arr = array($arr);
        }
        $this->breadcrumb = array_merge(array(array('name' => '首页', 'url' => U('Index/index'))), $arr);
    }

    //产品模块：微家校
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function init_wjx_nav(){
        $this->childnav(array(
            array('name' => '产品下载','url' => U('Product/wjx_download')),
            array('name' => '使用注意','url' => U('Product/wjx_faq')),
        ));
    }

    public function weijiaxiao()
    {
        $this->init_wjx_nav();
        $this->breadcrumb(array('name' => '微家校'));
        $this->page_full('weijiaxiao');
    }

    public function wjx_download()
    {
        $this->init_wjx_nav();
        $this->breadcrumb(array(array('name' => '微家校', 'url' => U('Product/weijiaxiao')), array('name' => '产品下载')));
        $this->page_full('wjx_download');
    }

    public function wjx_faq(){
        $this->init_wjx_nav();
        $this->breadcrumb(array(array('name' => '微家校', 'url' => U('Product/weijiaxiao')), array('name' => '使用注意')));
        $this->display('wjx_faq');
    }

    //产品模块：安心星
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function init_axx_nav(){
        $this->childnav(array(
            array('name' => '产品参数','url' => U('Product/axx_parameter')),
            array('name' => '使用注意','url' => U('Product/axx_faq')),
        ));
    }

    public function anxinxing()
    {
        $this->init_axx_nav();
        $this->breadcrumb(array('name' => '安心星'));
        $this->page_full('anxinxing');
    }

    public function axx_parameter()
    {
        $this->init_axx_nav();
        $this->breadcrumb(array(array('name' => '安心星', 'url' => U('Product/anxinxing')), array('name' => '产品参数')));
        $this->page_full('axx_parameter');
    }

    public function axx_faq(){
        $this->init_axx_nav();
        $this->breadcrumb(array(array('name' => '安心星', 'url' => U('Product/anxinxing')), array('name' => '使用注意')));
        $this->display('axx_faq');
    }
}