<?php

namespace Admin\Controller;

class IndexController extends BaseController
{
    protected function _initialize()
    {
        //parent:: _initialize();不继承
    }

    public function index()
    {
        $this->U_check();
        $this->controlid = 1;
        $this->user = $this->U_info();

        $this->display('index');
    }

    public function login()
    {
        if (IS_POST) {
            $verify = new \Think\Verify();
            if ($verify->check(I('post.verify'))) {
                $res = $this->admin()->login(I('post.loginuser'), I('post.password'));
                if ($res) {
                    $this->U_check_permissions();
                    $return['success'] = U('Index/index');
                } else {
                    $return['error'] = $this->admin()->getError();
                }
            } else {
                $return['error'] = $verify->getError();
            }
            echo json_encode($return);
        } else {
            layout('Layout/unlogin');
            $this->pageName = "登录";
            $this->display('login');
        }
    }

    /**
     * 生成验证码
     */
    public function verify()
    {
        $Verify = new \Think\Verify(array('length' => 4, 'useCurve' => false));
        $Verify->entry();
    }

    public function logout()
    {
        $this->admin()->logout();
        $this->redirect('Index/login');
    }

    public function test(){
        $this->display('test');
    }

}