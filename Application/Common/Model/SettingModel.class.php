<?php
namespace Common\Model;
use Think\Model;
/**
 * Class SettingModel
 * @package Common\Model
 */
class SettingModel extends Model
{

    const T_global = 0;
    const T_contact = 1;
    const T_flink = 2;


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
        if (is_array($keys)) $keys = implode(',', $keys);
        $res = $this->redis()->_hash('setting.' . $keys);
        if (empty($res)) {
            if (strpos($keys, ',')) {
                $where['key'] = array('in', $keys);
                $list = $this->field('key,value')->where($where)->select();
                if ($list) {
                    $res = array();
                    foreach ($list as $v) {
                        $res[$v['key']] = $v['value'];
                    }
                    $this->redis()->_hash('setting', $res);
                } else $res = null;
            } else {
                $where['key'] = $keys;
                $res = $this->field('value')->where($where)->find();
                if ($res) {
                    $res = $res['value'];
                    $this->redis()->_hash('setting.' . $keys, $res);
                }
            }
        }

        return $res;
    }

    //全局参数
    const type_global = 0;
    //查询全局参数的列表
    public function selectGlobal()
    {
        $where['type'] = self::type_global;
        $list = $this->field('id,key,value,describe,sort')->where($where)->select();

        return $list;
    }

    //联系电话
    const type_contact = 1;
    const redis_contact = 'contact';
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
        $list = S(self::redis_contact);
        if($list){
            $where['type'] = self::type_contact;
            $list = $this->field('key,value')->where($where)->order('sort')->select();
        }

        return $list;
    }

    //友情链接
    const type_Flink = 2;
    const redis_flink = 'flink';
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
        $list = S(self::redis_flink);
        if($list){
            $where['type'] = self::type_Flink;
            $list = $this->field('key,value')->where($where)->order('sort')->select();
        }

        return $list;
    }

}