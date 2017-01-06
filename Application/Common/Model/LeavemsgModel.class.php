<?php
namespace Common\Model;
use Think\Model;
/**
 * Class LeavemsgModel
 * @package Common\Model
 */
class LeavemsgModel extends Model
{

    protected $tableName = 'leave_msg';

    /**
     * @var array 自动验证
     */
    protected $_validate = array(
        array('name','require','请告诉我们您的称呼'), //默认情况下用正则进行验证
        array('mobile','/^1[345678][0-9]{9}$/','请输入有效的手机号','0','regex',1),
        array('msg', 'check_len', '留言长度小于200个字符', 0, 'callback', 3, array(0, 200)),
    );

    /**
     * 验证字符长度是否在某个区间，
     * @param $str
     * @param $min
     * @param $max
     * @return bool
     */
    function check_len($str, $min = 0, $max)
    {
        $len = $this->len($str);

        if ($len < $min || $len > $max) return false;
        return true;
    }

    /**
     * 获取utf-8下编码的字符长度
     * @param $str
     * @return int
     */
    function len($str)
    {
        if (!$str) {
            return 0;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, 'utf-8');
        } else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('ctime','time',self::MODEL_INSERT,'function'),    // 对ctime字段在插入的时候写入当前时间戳
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    const ip_null = 'unknown';

    //获取访问者的ip
    private function get_client_ip()
    {
        $ip = getenv("HTTP_CLIENT_IP");
        if (!$ip || !strcasecmp($ip, self::ip_null)){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
            if (!$ip || !strcasecmp($ip, self::ip_null)){
                $ip = getenv("REMOTE_ADDR");
                if (!$ip || !strcasecmp($ip, self::ip_null)){
                    $ip = $_SERVER['REMOTE_ADDR'];
                    if (!$ip || !strcasecmp($ip, self::ip_null)){
                        $ip = '';
                    }
                }
            }
        }
        return $ip;
    }

    //添加留言
    public function addMsg($name,$mobile,$msg,$device_type = 0)
    {
        $ip = $this->get_client_ip();
        if ($ip != self::ip_null) {
            $where['ip'] = $ip;
            $where['ctime'] = array('gt', time() - 3600 * 24);   //一天以内留言条数
            $n = $this->where($where)->count();
            if ($n >= 3) {
                $this->error = '你在一天之内的留言条数不能超过三条';
                write_log('留言过滤 [LeavemsgModel->addMsg]', array(
                    '留言IP' => $ip,
                    '留言人' => $name,
                    '手机号' => $mobile,
                    '留言内容' => $msg,
                ));
                return false;
            }
        }

        $data['ip'] = $ip;
        $data['name'] = $name;
        $data['mobile'] = $mobile;
        $data['msg'] = $msg;
        $data['device_type'] = $device_type;

        if ($this->create($data)) {
            $res = $this->add();
            return $res;
        }

        return false;
    }

    //查询留言列表
    public function selectList($where = array(),$page_index = 0,$page_size = 10,$order = 'status,ctime desc')
    {
        $count = $this->where($where)->count();
        $list = $this->field('id,ip,name,mobile,msg,device_type,ctime,status')->where($where)->order($order)
            ->limit($page_index * $page_size, $page_size)
            ->select();

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
            }
        } else {
            $list = array();
        }

        $result = array('list' => $list, 'count' => ceil($count / $page_size));

        return $result;
    }

    //标记为已读
    public function setRead($id){
        $where['id'] = $id;
        $data['status'] = 1;
        if ($this->create($data, self::MODEL_UPDATE)) {
            $res = $this->where($where)->save();
            return !!$res;
        }
    }

    //标记为已读
    public function delMsg($id){
        $where['id'] = $id;
        $data['status'] = 9;
        if ($this->create($data, self::MODEL_UPDATE)) {
            $res = $this->where($where)->save();
            return !!$res;
        }
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
        $where['status'] = array('neq',9);
        $res = $this->field("count(*) as n,FLOOR(ctime / $s) as t")->where($where)->group("t")->select();


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