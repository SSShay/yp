<?php

namespace Admin\Controller;

use Common\Model\BannerModel;
use Common\Model\MenuModel;
use Common\Model\SettingModel;

class ManageController extends BaseController
{

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  菜单管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function menu()
    {
        $this->U_check_permissions();
        $menu_obj = new MenuModel();
        $menus = $menu_obj->selectList();
        $this->menu = json_encode($menus);
        $this->display('menu');
    }

    public function menu_add()
    {
        $this->U_check_permissions('menu');
        $menu_obj = new MenuModel();
        $res = $menu_obj->addMenu(I('post.name'), I('post.sort'), I('post.link'), I('post.topid'), I('post.sitemap'));
        $this->clear_menu();
        echo json_encode(array('id' => $res));
    }

    public function menu_del()
    {
        $this->U_check_permissions('menu');
        $menu_obj = new MenuModel();
        $res = $menu_obj->delMenu(I('post.id'));
        $this->clear_menu();
        echo json_encode(array('success' => $res));
    }

    public function menu_mod()
    {
        $this->U_check_permissions('menu');
        if(IS_POST) {
            $change = I('post.change');
            $menu_obj = new MenuModel();
            $return['fail'] = 0;
            $return['count'] = count($change);
            foreach ($change as $v) {
                $where['id'] = $v['id'];
                $v['utime'] = time();
                unset($v['id']);
                $res = $menu_obj->setObj($where, $v);
                if (!$res) $return['fail']++;
            }

            $this->clear_menu();
            echo json_encode($return);
        }
    }

    function clear_menu(){
        session('menu', null);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  轮播图管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function banner()
    {
        $this->U_check_permissions();

        $banner_obj = new BannerModel();
        $banner = $banner_obj->selectList();
        $this->banner = json_encode($banner);

        $this->display('banner');
    }

    //轮播图上传
    public function banner_upload()
    {
        $this->U_check_permissions('banner');
        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 512000,//最大上传文件大小不超过500K
                'rootPath' => 'Uploads',
                'savePath' => '/banner/',
                'saveName' => array('uniqid'),
                'exts' => array('jpg'),
                'autoSub' => false,
                'subName' => array('date', 'Ymd'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['img'];
                //返回文件地址和名给JS作回调用
                $img = 'Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                $id = I('post.id');
                $banner_obj = new BannerModel();
                if ($id) {
                    $res = $banner_obj->setBanner($id, $img);
                } else {
                    $res = $banner_obj->addBanner($img, I('post.sort'));
                    if ($res) $result['id'] = $res;
                }
                if ($res) {
                    $result['img'] = $img;
                    $this->clear_banner();
                } else $result['error'] = $banner_obj->getError();
            } else {
                $result['error'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['error'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    //轮播图删除
    public function banner_del()
    {
        $this->U_check_permissions('banner');
        if (IS_AJAX) {
            $banner_obj = new BannerModel();
            $res = $banner_obj->delBanner(I('post.id'));
            if ($res) {
                $r['success'] = true;
                $this->clear_banner();
            } else {
                $r['error'] = $banner_obj->getError();
            }
            echo json_encode($r);
        }
    }

    //轮播图修改
    public function banner_mod()
    {
        $this->U_check_permissions('banner');
        if (IS_POST) {
            $change = I('post.change');
            $banner_obj = new BannerModel();
            $return['fail'] = 0;
            $return['count'] = count($change);
            foreach ($change as $v) {
                $where['id'] = $v['id'];
                $v['utime'] = time();
                unset($v['id']);
                $res = $banner_obj->setObj($where, $v);
                if (!$res) $return['fail']++;
            }

            $this->clear_banner();
            echo json_encode($return);
        }
    }

    private function clear_banner(){
        session('banner_index', null);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  参数设置
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function setting_global()
    {
        $this->U_check_permissions();
        $setting_obj = new SettingModel();
        $this->globals = $setting_obj->selectGlobal();

        $this->display('setting_global');
    }

    public function setting_contact()
    {
        $this->U_check_permissions();
        $setting_obj = new SettingModel();
        $this->contacts = $setting_obj->selectContact();

        $this->display('setting_contact');
    }

    public function setting_flink()
    {
        $this->U_check_permissions();
        $setting_obj = new SettingModel();
        $this->flinks = $setting_obj->selectFlink();

        $this->display('setting_flink');
    }

    private function check_setting_permissions($type = '')
    {
        $list = array('setting_global', 'setting_contact', 'setting_flink');
        if ($type === '') $type = I('get.type');
        if (!is_numeric($type)) $this->error('TYPE 参数缺失');
        $p = $list[$type];
        if (!$p) $this->error('TYPE 参数不合法');
        $this->U_check_permissions($p);

        return $type;
    }

    // 添加设置
    public function setting_add()
    {
        $this->check_setting_permissions(I('post.type'));
        $setting_obj = new SettingModel();
        $res = $setting_obj->addObj($_POST);
        if($res){
            $re['success'] = $res;
        }else{
            $re['error'] = $setting_obj->getError();
        }
        echo json_encode($re);
    }

    // 保存设置
    public function setting_save()
    {
        $where['type'] = $this->check_setting_permissions();
        $setting_obj = new SettingModel();
        $where['id'] = I('post.id');
        unset($_POST['id']);

        $res = $setting_obj->setObj($where, $_POST);
        if($res){
            $re['success'] = $res;
        }else{
            $re['error'] = $setting_obj->getError();
        }
        echo json_encode($re);
    }

    // 删除设置项
    public function setting_del()
    {
        $where['type'] = $this->check_setting_permissions();
        $setting_obj = new SettingModel();
        $where['id'] = I('post.id');
        $res = $setting_obj->deleteObj($where);
        if($res){
            $re['success'] = $res;
        }else{
            $re['error'] = $setting_obj->getError();
        }
        echo json_encode($re);
    }

}