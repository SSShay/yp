<?php
namespace Common\Model;
use Think\Model;
/**
 * Class MenuModel
 * @package Common\Model
 */
class BrowserModel extends Model
{

    protected $tableName = 'browser';

    /**
     * @var array 自动验证
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('time', 'time', self::MODEL_INSERT, 'function'),
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const ip_null = 'unknown';

    //获取访问者的ip
    private function get_client_ip()
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

    //添加浏览记录
    public function addBrowser()
    {
        $ip = $this->get_client_ip();
        $data['ip'] = $ip;
        $res = $this->addObj($data);
        if ($res) {
            $browsing_history_obj = new BrowsingHistoryModel();
            $browsing_history_obj->addHistory($res);
        } else {
            write_log('浏览记录', array(
                '错误' => '浏览者保存失败',
                '浏览者IP' => $ip,
                '时间' => NOW_TIME
            ));
        }
        return $res;
    }

    /**
     * 统计$limit个每$s秒的记录条数
     * @param string $endtime
     * @param int $s 默认为1个小时
     * @param int $limit 默认为24个小时（一天）
     * @return array
     */
    public function countByTime($s = 3600, $limit = 24, $endtime = '')
    {
        if (!$endtime) $endtime = ceil(NOW_TIME / 3600 / 24) * 3600 * 24 - 8 * 3600;
        $where['time'] = array('gt', $endtime - $limit * $s);
        $res = $this->field("count(*) as n,FLOOR(time / $s) as t")->where($where)->group("t")->select();

        if (!$res) $list = array();
        else {
            foreach ($res as $v) {
                $list[$v['t']] = $v['n'];
            }
        }
        $data = array();
        for ($t = $endtime - $s * $limit; $t < $endtime; $t += $s) {
            $n = $list[ceil($t / $s)];
            if (!isset($n)) $n = 0;
            $data[] = array($t * 1000, $n);
        }

        return $data;
    }

}