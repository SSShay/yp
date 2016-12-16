<?php
namespace Common\Model;
use Think\Model;
/**
 * Class ScOrderModel
 * @package Common\Model
 */
class ScOrderModel extends Model
{

    protected $tableName = 'sc_order';

    /**
     * @var array 自动验证
     */
    protected $_validate = array(
        array('addressee','require','收货人不能为空'), //默认情况下用正则进行验证
        array('mobile','require','手机号不能为空'), //默认情况下用正则进行验证
        array('mobile','/^1[345678][0-9]{9}$/','请输入有效的手机号','0','regex',1),
        array('area_id','require','所在区域不能为空'), //默认情况下用正则进行验证
        array('addr_detail','require','详细地址不能为空'), //默认情况下用正则进行验证
    );

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('ctime','time',self::MODEL_INSERT,'function'),    // 对ctime字段在插入的时候写入当前时间戳
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

    public function addOrder($addressee,$mobile,$area_id,$addr_detail)
    {
        $data['addressee'] = $addressee;
        $data['mobile'] = $mobile;
        $data['area_id'] = $area_id;
        $data['addr_detail'] = $addr_detail;

        if ($this->create($data)) {
            $res = $this->add();
            return $res;
        }
        return false;
    }

    public function setTotal($id,$total,$describe)
    {
        $where['id'] = $id;

        $data['total'] = $total;
        $data['describe'] = $describe;

        if ($this->create($data, self::MODEL_UPDATE)) {
            $res = $this->where($where)->save();
            return !!$res;
        }
        return false;
    }
}