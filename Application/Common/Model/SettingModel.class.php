<?php
namespace Common\Model;
use Think\Model;
/**
 * Class SettingModel
 * @package Common\Model
 */
class SettingModel extends Model
{

    protected $tableName = 'setting';

    /**
     * @var array 自动验证
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('utime', 'time', self::MODEL_INSERT, 'function'),
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    public function selectVal($keys)
    {
        if (is_array($keys) || strpos($keys, ',')) {
            $where['key'] = array('in', $keys);
            $list = $this->field('key,value')->where($where)->select();
            return $list;
        } else {
            $where['key'] = $keys;
            $list = $this->field('key,value')->where($where)->find();
            return $list ? $list[0]['value'] : '';
        }
    }

    //全局参数
    const type_global = 0;
    //查询联系方式的列表
    public function selectGlobal()
    {
        $where['type'] = self::type_global;
        $list = $this->field('id,key,value,describe,sort')->where($where)->select();

        return $list;
    }
    //查询联系方式的列表（Home模块）
    public function selectGlobalByHome()
    {
        $where['type'] = self::type_Flink;
        $list = $this->field('key,value')->where($where)->order('sort')->select();

        return $list;
    }

    //联系电话
    const type_contact = 1;
    //查询联系方式的列表
    public function selectContact()
    {
        $where['type'] = self::type_contact;
        $list = $this->field('id,key,value,describe,sort')->where($where)->order('sort')->select();

        return $list;
    }
    //查询联系方式的列表（Home模块）
    public function selectContactByHome()
    {
        $where['type'] = self::type_contact;
        $list = $this->field('key,value')->where($where)->order('sort')->select();

        return $list;
    }

    //友情链接
    const type_Flink = 2;
    //查询联系方式的列表
    public function selectFlink()
    {
        $where['type'] = self::type_Flink;
        $list = $this->field('id,key,value,describe,sort')->where($where)->order('sort')->select();

        return $list;
    }
    //查询联系方式的列表（Home模块）
    public function selectFlinkByHome()
    {
        $where['type'] = self::type_Flink;
        $list = $this->field('key,value')->where($where)->order('sort')->select();

        return $list;
    }

}