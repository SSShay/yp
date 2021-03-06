<?php

namespace Mobile\Controller;

use Common\Model\LeavemsgModel;
use Common\Model\SettingModel;

class IndexController extends BaseController
{
    //首页
    public function index()
    {
        $this->display('index');
    }

    //留言
    public function leave_msg()
    {
        if (IS_POST) {
            $bid = $this->bid();
            if($bid){
                $leave_msg_obj = new LeavemsgModel();
                $res = $leave_msg_obj->addMsg($bid, I('post.name'), I('post.mobile'), I('post.msg'));
                if ($res) {
                    $re['success'] = true;
                } else {
                    $re['error'] = $leave_msg_obj->getError();
                }
                echo json_encode($re);
            }
        } else {
            $setting_obj = new SettingModel();
            $this->kefu_mobile = $setting_obj->selectVal('kefu-mobile');

            $this->page_single('leave_msg');
        }
    }

    //手机投放页面
    public function agent()
    {
        if (IS_POST) {
            $bid = $this->bid();
            if($bid){
                $leave_msg_obj = new LeavemsgModel();
                $res = $leave_msg_obj->addMsg($bid, I('post.name'), I('post.mobile'), I('post.msg'));
                if ($res) {
                    $re['success'] = true;
                } else {
                    $re['error'] = $leave_msg_obj->getError();
                }
                echo json_encode($re);
            }
        } else {
            $setting_obj = new SettingModel();
            $this->kefu_mobile = $setting_obj->selectVal('kefu-mobile');

            $this->page_single('agent');
        }
    }

}