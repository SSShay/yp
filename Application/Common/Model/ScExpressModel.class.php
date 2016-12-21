<?php
namespace Common\Model;
use Think\Model;
/**
 * 快递单号模型
 * Class ScExpressModel
 * @package Common\Model
 */
class ScExpressModel extends Model
{

    protected $tableName = 'sc_express';

    /**
     * @var array 自动验证
     */
    protected $_validate = array(
        array('number','require','收货人不能为空'),    //不为空
    );

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('ctime','time',self::MODEL_INSERT,'function'),    // 对ctime字段在插入的时候写入当前时间戳
        array('utime','time',self::MODEL_UPDATE,'function'),    // 对ctime字段在插入的时候写入当前时间戳
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*//查询列表
    public function selectList($where = array(),$page_index = 0,$page_size = 10,$order = 'ctime desc')
    {
        $sc_order = $this->getTableName();
        $sc_area = M("Area")->getTableName();
        $sc_pay = M("ScPay")->getTableName();
        $count = $this->where($where)->count();
        $list = $this->field("$sc_order.id,addressee,total,describe,$sc_order.ctime,status")
            ->where($where)
            ->join("LEFT JOIN $sc_pay ON orderid = $sc_order.id")
            ->order($order)
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

    //
    public function selectListByHome()
    {
        $where['display'] = 1;
        $list = $this->field('id,name,thumb,price')->where($where)->order('sort')->select();

        return $list;
    }*/

    public function findExpress($orderid,$field = 'number,ctime,utime,status')
    {
        $res = $this->field($field)->where(array('order_id' => $orderid))->find();
        if ($res) {
            $res['ctime'] = date('Y-m-d H:i:s', $res['ctime']);
            if ($res['utime']) $res['utime'] = date('Y-m-d H:i:s', $res['utime']);
        }
        return $res;
    }


    public function addExpress($orderid,$number)
    {
        $data['order_id'] = $orderid;
        $data['number'] = $number;

        if ($this->create($data)) {
            $res = $this->add();
            return $res;
        }
        return false;
    }
}