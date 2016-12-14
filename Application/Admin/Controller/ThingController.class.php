<?php

namespace Admin\Controller;

use Common\Model\LeavemsgModel;

class ThingController extends BaseController
{

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  留言管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const page_key = 'leave_msg-page-index';

    //留言管理页面
    public function leave_msg()
    {
        $this->U_check_permissions();

        $this->page_index = session(self::page_key);
        if(!$this->page_index) $this->page_index = 0;

        $this->display('leave_msg');
    }

    //留言列表
    public function leave_msg_list()
    {
        $this->U_check_permissions('leave_msg');

        $p = I('get.p');
        session(self::page_key, $p);
        $msg_obj = new LeavemsgModel();
        $res = $msg_obj->selectList(array('status' => array('neq', 9)), $p);
        echo json_encode($res);
    }

    //标为已读
    public function leave_msg_setread()
    {
        $id = I('post.id');
        $msg_obj = new LeavemsgModel();
        $res = $msg_obj->setRead($id);
        echo json_encode(array('success' => $res));
    }

    //删除留言
    public function leave_msg_del()
    {
        $id = I('post.id');
        $msg_obj = new LeavemsgModel();
        $res = $msg_obj->delMsg($id);
        echo json_encode(array('success' => $res));
    }

}