<?php
namespace Common\Model;
use Think\Model;
/**
 * Class ScOrderProductModel
 * @package Common\Model
 */
class ScOrderProductModel extends Model
{

    protected $tableName = 'sc_order_product';

    /**
     * @var array 自动验证
     */
    protected $_validate = array(
        array('count','require','购买数量不能为空'), //默认情况下用正则进行验证
    );

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array();

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //查询列表
    public function selectByOrder($order_id,$field = 'describe,price,count',$order = '')
    {
        $where['order_id'] = $order_id;
        $list = $this->field($field)->where($where)->order($order)->select();

        return $list;
    }

    /*//
    public function selectListByHome()
    {
        $where['display'] = 1;
        $list = $this->field('id,name,thumb,price')->where($where)->order('sort')->select();

        return $list;
    }*/
}