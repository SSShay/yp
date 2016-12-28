<?php

namespace Home\Controller;
use Common\Model\BrowserModel;
use Common\Model\BrowsingHistoryModel;
use Common\Model\MenuModel;
use Common\Model\SettingModel;
use Think\Controller;

class BaseController extends Controller
{
    protected $breadcrumb = array();
    protected $childnav = array();

    public function __construct()
    {
        parent::__construct();
    }

    protected function _initialize()
    {

    }

    protected function page_full($tpl)
    {
        $this->nofoot = true;
        layout('Layout/pagefull');
        $this->display($tpl);
    }

    protected function page_nofoot($tpl)
    {
        $this->nofoot = true;
        layout('Layout/pagenofoot');
        $this->display($tpl);
    }

    protected function _before_display()
    {
        //菜单
        $menu_list = session('menu');
        if (!isset($menu_list)) {
            $menu_obj = new MenuModel();
            $menu_list = $menu_obj->selectListByHome();
            session('menu', $menu_list);
        }

        //绑定菜单的数据
        $this->assign('menus', $menu_list);
        $this->assign('breadcrumb', $this->breadcrumb);
        $this->assign('childnav', $this->childnav);

        //绑定footer的数据
        if (!$this->nofoot) {
            $setting_obj = new SettingModel();
            $this->links = $setting_obj->selectFlinkByHome();
            $this->foot_txt = $setting_obj->selectVal('foot-text');

            $kefu['qq'] = $setting_obj->selectVal('kefu-qq');
            $kefu['mobile'] = $setting_obj->selectVal('kefu-mobile');
            $this->kefu = $kefu;
        }

        //title、如果在打印之前设置好title则不自动创建
        if (!$this->title && !empty($this->breadcrumb)) {
            $list = array();
            foreach ($this->breadcrumb as $v) {
                if ($v['name'] != '首页') $list[] = $v['name'];
            }
            $this->title = implode(' - ', $list);
        }

        //记录用户信息
        $this->record();
    }

    protected function breadcrumb($arr = array())
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                $arr = array(array('name' => $arr));
            } elseif (!is_array($arr[0])) $arr = array($arr);
        }
        $this->breadcrumb = array_merge(array(array('name' => '首页', 'url' => U('Index/index'))), $arr);

    }

    protected function childnav($arr = array())
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                $arr = array(array('name' => $arr));
            } elseif (!is_array($arr[0])) $arr = array($arr);
        }
        $this->childnav = array_reverse($arr);
    }

    public function _empty($msg = '当前没有更多内容')
    {
        if (IS_AJAX) {
            echo json_encode(array('error' => $msg));
        } else {
            echo $msg;
        }
        exit;
    }

    //用户session id;
    private function bid($bid = '')
    {
        if ($bid) session('bid', $bid);
        else return session('bid');
    }

    //记录浏览痕迹
    private function record()
    {
        $bid = $this->bid();
        if (!$bid) {
            $browser_obj = new BrowserModel();
            $bid = $browser_obj->addBrowser();
            if ($bid) $this->bid($bid);
        } else {
            $browser_history_obj = new BrowsingHistoryModel();
            $browser_history_obj->addHistory($bid);
        }
    }

    const ip_null = 'unknown';

    //获取访问者的ip
    protected function get_client_ip()
    {
        $ip = getenv("HTTP_CLIENT_IP");
        if (!$ip || !strcasecmp($ip, self::ip_null)) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
            if (!$ip || !strcasecmp($ip, self::ip_null)) {
                $ip = getenv("REMOTE_ADDR");
                if (!$ip || !strcasecmp($ip, self::ip_null)) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                    if (!$ip || !strcasecmp($ip, self::ip_null)) {
                        $ip = '';
                    }
                }
            }
        }
        return $ip;
    }

    protected function en_submit_id()
    {
        $this->submit_id = md5($this->get_client_ip() . NOW_TIME);
    }

}