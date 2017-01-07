<?php

namespace Mobile\Controller;

use Common\Model\BrowserModel;
use Common\Model\BrowsingHistoryModel;
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

    //单页模板
    protected function page_single($tpl)
    {
        $this->nofoot = true;
        layout('Layout/pagesingle');
        $this->display($tpl);
    }

    protected function _before_display()
    {
        //记录用户信息
        $this->record();
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

}