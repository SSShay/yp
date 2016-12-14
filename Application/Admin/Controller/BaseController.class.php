<?php

namespace Admin\Controller;
use Admin\Model\AdminModel;
use Admin\Model\AdminPermissionsModel;
use Think\Controller;

class BaseController extends Controller
{

    protected function _initialize()
    {
        $this->U_check();
    }

    protected function _before_display()
    {
        if ($this->is_login) {
            $this->admin_info = $this->U_info();
            $permissions = $this->U_permissions();

            if($this->control && $this->action){
                $control = $permissions[$this->control];
                $action = $control['list'][$this->action];
                $this->controlid = $control['id'];
                $this->actionid = $action['id'];

                if(!isset($this->pageName)) $this->pageName = $control['name'].' - '.$action['name'];
            }

            foreach ($permissions as $cn => $c) {
                if ($c['sort'] <= 0) {
                    unset($permissions[$cn]);
                } else {
                    foreach ($c['list'] as $an => $a) {
                        if ($a['sort'] <= 0) {
                            unset($permissions[$cn]['list'][$an]);
                        }
                    }
                }
            }
            $this->permissions = $permissions;
        }
    }

    protected $is_login;
    //验证管理员是否合法
    protected function U_check()
    {
        $admin = $this->U_info();
        if (!isset($admin)) {
            $this->redirect('Index/login');
        }
        $this->is_login = true;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //管理员对象
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private $admin;

    protected function admin(){
        if(!isset($this->admin)) $this->admin = new AdminModel();

        return $this->admin;
    }

    private $u_permissions;

    //获取权限列表
    protected function U_permissions()
    {
        if (!isset($this->u_permissions)) $this->u_permissions = session('permissions');
        return $this->u_permissions;
    }

    //权限检查
    /**
     * 检查是否有指定控制器的指定动作的权限
     * @param string $action 指定动作，默认为当前动作
     * @param string $control 指定控制器，默认为当前控制器
     */
    protected function U_check_permissions($action = '',$control = '')
    {
        if(!$action) $action = ACTION_NAME;
        if(!$control) $control = CONTROLLER_NAME;
        $this->action = $action;
        $this->control = $control;
        $up = $this->U_permissions();
        if (!isset($up[$control]) || !isset($up[$control]['list'][$action])) {
            $ap_obj = new AdminPermissionsModel();
            $ap = $ap_obj->getName($action,$control);
            $this->error('你没有 ' . $ap['cname'] . ' - ' . $ap['aname'] . ' 的权限，如有疑问请联系管理员','权限不足');
        }
    }


    private $u_info;

    //获取权限列表
    protected function U_info($key = '')
    {
        if (!isset($this->u_info)) $this->u_info = session('admin');
        return $key ? $this->u_info[$key] : $this->u_info;
    }

    protected function error($msg = '未知错误',$title = '出错了')
    {
        if (IS_AJAX) {
            echo json_encode(array('error' => $msg));
        } else {
            $this->title = $title;
            $this->error = $msg;
            $this->user = $this->U_info();
            $this->display('Empty:error');
        }
        exit;
    }

}




