<?php

namespace Admin\Controller;
use Common\Model\BrowserModel;
use Common\Model\BrowsingHistoryModel;
use Common\Model\LeavemsgModel;

/**
 * 网站数据分析
 * Class DataController
 * @package Admin\Controller
 */
class AnalysisController extends BaseController
{

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function index()
    {
        $this->U_check_permissions('index');

        $this->display('index');
    }

    public function get_analytic()
    {
        $data = array();
        $s = I('get.span');
        $limit = I('get.limit');
        $browsing_history_obj = new BrowsingHistoryModel();
        $data['PV'] = $browsing_history_obj->countByTime($s, $limit);

        $browser_obj = new BrowserModel();
        $data['UV'] = $browser_obj->countByTime($s, $limit);

        $leavemsg_obj = new LeavemsgModel();
        $data['MV'] = $leavemsg_obj->countByTime($s, $limit);

        echo json_encode($data);
    }

}