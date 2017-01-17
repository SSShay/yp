<?php
namespace Common\Model;
use Think\Model;
/**
 * Class ScProductModel
 * @package Common\Model
 */
class ScProductModel extends Model
{

    protected $tableName = 'sc_product';

    /**
     * @var array 自动验证
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('ctime','time',self::MODEL_INSERT,'function'),    // 对ctime字段在插入的时候写入当前时间戳
        array('utime','time',self::MODEL_BOTH,'function'),      // 对utime字段在修改的时候写入当前时间戳
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //查询文章列表
    public function selectList($where = array(),$page_index = 0,$page_size = 10,$order = 'ctime desc')
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

    const redis_product_list = 'product_list';
    //产品列表
    public function selectListByHome()
    {
        $list = S(self::redis_product_list);
        if (!$list) {
            $where['display'] = 1;
            $list = $this->field('id,name,thumb,price')->where($where)->order('sort')->select();
            S(self::redis_product_list, $list);
        }

        return $list;
    }

    const redis_product_top = 'product_top';
    //人气推荐5个
    public function selectListTop($limit = 5)
    {
        $list = S(self::redis_product_top);
        if (!$list) {
            $where['display'] = 1;
            $list = $this->field('id,name,thumb,price')->where($where)->limit($limit)->order('hot desc')->select();
            S(self::redis_product_top, $list);
        }

        return $list;
    }

    public static function clear_redis()
    {
        S(self::redis_product_list, null);
        S(self::redis_product_top, null);
    }
}