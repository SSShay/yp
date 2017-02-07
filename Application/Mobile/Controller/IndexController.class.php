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


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //四大入口
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function school(){
        //$this->breadcrumb(array('name' => '学校'));
        //$this->page_nofoot('school');
    }

    public function agent()
    {
        /*$this->breadcrumb(array('name' => '代理商'));
        if($this->is_mobile()){
            $this->redirect('Index/agent_m');
        }else{
            $this->page_nofoot('agent');
        }*/
    }

}