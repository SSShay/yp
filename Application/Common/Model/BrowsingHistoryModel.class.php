<?php
namespace Common\Model;
use Think\Model;
/**
 * Class MenuModel
 * @package Common\Model
 */
class BrowsingHistoryModel extends Model
{

    protected $tableName = 'browsing_history';

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

    //添加文章
    public function addHistory($bid)
    {
        $data['bid'] = $bid;
        $data['action'] = CONTROLLER_NAME . '/' . ACTION_NAME;
        $data['url'] = $_SERVER['REQUEST_URI'];
        $data['referer'] = $_SERVER['HTTP_REFERER'];
        $res = $this->addObj($data);
        if (!$res) {
            write_log('浏览记录', array(
                '错误' => '首次浏览记录添加失败',
                '浏览者ID' => $bid,
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
        if (!$endtime) $endtime = ceil(NOW_TIME / 3600 / 24) * 3600 * 24  - 8 * 3600;
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