<?php

namespace Home\Controller;
use Common\Model\ArticleModel;

/**
 * 产品介绍
 * Class ProductController
 * @package Home\Controller
 */
class NewsController extends BaseController
{

    protected function _before_display()
    {
        parent::_before_display();

        //设置导航栏的active标记
        $this->indexnav = '新闻中心';
    }

    protected function breadcrumb($arr = array())
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                $arr = array(array('name' => $arr));
            } elseif (!is_array($arr[0])) $arr = array($arr);
        }
        $this->breadcrumb = array_merge(array(array('name' => '新闻中心')), $arr);
    }

    //模块导航初始化
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function init_nav()
    {
        $this->childnav(array(
            array('name' => '公司新闻', 'url' => U('News/index', array('type' => 0))),
            array('name' => '行业动态', 'url' => U('News/index', array('type' => 1))),
            array('name' => '政策支持', 'url' => U('News/index', array('type' => 2))),
        ));
    }

    public function index()
    {
        $this->init_nav();
        $tlist = array('公司新闻', '行业动态', '政策支持');
        $this->type = I('get.type');
        $this->typestr = $tlist[$this->type];
        if (!$this->typestr) $this->_empty();

        $this->breadcrumb(array('name' => $this->typestr));
        $this->display('list');
    }

    //文章
    public function article()
    {

        $id = I('get.id');
        $article_obj = new ArticleModel();
        $article = $article_obj->findObj(array('id' => $id), 'type,thumb,title,ctime');
        if (!$article) $this->_empty('找不到文章...');
        $article['time'] = date('Y-m-d H:i', $article['ctime']);
        $t = $article['type'];

        $tlist_en = array('CompanyNews', 'IndustryDynamics', 'PolicySupport');
        $t_en = $tlist_en[$t];
        if (!$t_en) $this->_empty('找不到文章...，文章类型错误！');

        $this->init_nav();
        $tlist = array('公司新闻', '行业动态', '政策支持');
        $t_ch = $tlist[$t];

        $this->breadcrumb(array('name' => $t_ch, 'url' => U('News/index', array('type' => $t))));
        $this->article = $article;
        $this->title = $article['title'];

        layout('Layout/pagearticle');
        $this->display('News/' . $t_en . '/' . $id);
    }

    public function article_list()
    {
        $article_obj = new ArticleModel();
        $p = I('get.p');
        $t = I('get.type');
        $article = $article_obj->selectListByHome($t, $p);
        echo json_encode($article);
    }
}