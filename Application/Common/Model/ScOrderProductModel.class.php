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
}