<?php

namespace Home\Controller;

use Common\Model\ArticleModel;
use Common\Model\BannerModel;
use Common\Model\LeavemsgModel;
use Common\Model\SettingModel;
use Common\Model\VideoModel;

class IndexController extends BaseController
{
    //首页
    public function index()
    {
        //轮播图
        $banner_list = session('banner_index');
        if (!isset($banner_list)) {
            $banner_obj = new BannerModel();
            $banner_list = $banner_obj->selectListByHome();
            session('banner_index', $banner_list);
        }
        $this->banner = $banner_list;

        $article_obj = new ArticleModel();
        $this->news = $article_obj->selectListByIndex();

        $video_obj = new VideoModel();
        $this->video = $video_obj->getFirst();

        $setting_obj = new SettingModel();
        $this->title = $setting_obj->selectVal('title');
        $this->keywords = $setting_obj->selectVal('keywords');
        $this->description = $setting_obj->selectVal('description');

        $this->display('index');
    }

    //宣传视频页面
    public function video()
    {
        $video_obj = new VideoModel();
        $this->video = $video_obj->selectListByHome();

        $this->breadcrumb(array('name' => '宣传视频'));

        //设置导航栏的active标记
        $this->indexnav = '宣传视频';
        $this->display('video');
    }

    //留言
    public function leave_msg()
    {
        if (IS_POST) {
            $leave_msg_obj = new LeavemsgModel();
            $res = $leave_msg_obj->addMsg(I('post.name'),I('post.mobile'),I('post.msg'));
            if($res){
                $re['success'] = true;
            }else{
                $re['error'] = $leave_msg_obj->getError();
            }
            echo json_encode($re);
        } else {
            $this->breadcrumb(array('name' => '填写信息'));
            $this->en_submit_id();
            $this->page_full('leave_msg');
        }
    }

    //联系我们
    public function contact_us()
    {
        $setting_obj = new SettingModel();
        $this->contacts = $setting_obj->selectContactByHome();
        $this->display('contact_us');
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //四大入口
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function school(){
        $this->breadcrumb(array('name' => '学校'));
        $this->en_submit_id();
        $this->page_nofoot('school');
    }

    public function agent(){
        $this->breadcrumb(array('name' => '代理商'));
        $this->en_submit_id();
        $this->page_nofoot('agent');
    }

    public function edu_bureau(){
        $this->breadcrumb(array('name' => '教育局'));
        $this->en_submit_id();
        $this->page_nofoot('edu_bureau');
    }

    public function trade_union(){
        $this->breadcrumb(array('name' => '行业联盟'));
        $this->en_submit_id();
        $this->page_nofoot('trade_union');
        //$this->page_full('trade_union');
    }

    //根据ip获取地址
    public function getLocation()
    {
        $ip = get_client_ip(); //获取当前用户的ip
        $ip = '115.200.232.214';
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
        $data = file_get_contents($url); //调用淘宝接口获取信息
        echo $data;
    }

}