<?php
namespace Common\Model;
use Think\Model;
/**
 * Class ScPayModel
 * @package Common\Model
 */
class ScPayModel extends Model
{

    /**
     * 交易状态
     * https://doc.open.alipay.com/docs/doc.htm?spm=a219a.7629140.0.0.0f68ba&treeId=62&articleId=104743&docType=1#s7
     */
    const T_paying = 0;               //交易完成
    const T_pay_success = 1;         //交易成功
    const T_refunding = 2;           //退款中
    const T_refund_success = 3;     //退款成功
    const T_pay_completion = 9;     //交易完成

    /**
     * 支付类型
     */
    const PAY_ali = 1;  //支付宝支付
    const PAY_wx = 2;   //微信支付

    protected $tableName = 'sc_pay';

    /**
     * @var array 自动验证
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('paytime','time',self::MODEL_INSERT,'function'),    // 对paytime字段在插入的时候写入当前时间戳
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //查询列表
    /*public function selectList($where = array(),$page_index = 0,$page_size = 10,$order = 'ctime desc')
    {
        $count = $this->where($where)->count();
        $list = $this->field('id,thumb,name,price,display,sort,ctime,utime')->where($where)->order($order)
            ->limit($page_index * $page_size, $page_size)
            ->select();
        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['ctime'] = date('Y-m-d H:i', $v['ctime']);
                $list[$k]['utime'] = date('Y-m-d H:i', $v['utime']);
            }
        }else{
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

    public function findPay($orderid,$field = 'pay_type,trade_id,status,paytime')
    {
        $where['order_id'] = $orderid;
        $res = $this->field($field)->where($where)->find();

        return $res;
    }

    public static function getType($pay_type)
    {
        switch ($pay_type) {
            case self::PAY_ali:
                return '支付宝';
            case self::PAY_wx:
                return '微信';
            default:
                return 'ERROR';
        }
    }

    public function addPay($orderid,$pay_type,$trade_id,$iscallback = false,$status = self::T_pay_success)
    {
        $data['order_id'] = $orderid;
        $data['pay_type'] = $pay_type;
        $data['trade_id'] = $trade_id;
        $data['status'] = $status;
        if ($iscallback) $data['callback_time'] = NOW_TIME;

        if ($this->create($data)) {
            $res = $this->add();
            return $res;
        }
        return false;
    }

    /**
     * 交易完成
     * @param $id
     * @return bool
     */
    public function setCompletion($id)
    {
        $where['order_id'] = $id;
        $where['status'] = ScPayModel::T_pay_success;
        $data['status'] = ScPayModel::T_pay_completion;
        $data['callback_time'] = time();

        if ($this->create($data, self::MODEL_UPDATE)) {
            $res = $this->where($where)->save();
            return !!$res;
        }
        return false;
    }
}