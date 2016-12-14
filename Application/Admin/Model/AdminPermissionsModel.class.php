<?php
namespace Admin\Model;
use Think\Model;
/**
 * 管理员权限模型
 * Class AdminPermissionsModel
 * @package Common\Model
 */
class AdminPermissionsModel extends Model
{

    protected $tableName = 'admin_permissions';

    /**
     * @var array 自动验证
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array();


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function selectList($str)
    {
        $where['A.controlid'] = array('neq', 0);
        if ($str != 'MAX') {
            $where['A.id'] = array('in', $str);
        }

        $tname = $this->getTableName();
        $res = $this->field('C.id cid,C.name as c,C.describe as cd,C.menusort as cms,C.icon as cicon,A.id,A.name as a,A.describe,A.menusort as ams,A.icon as aicon')
            ->alias('A')->where($where)->join($tname . ' C ON C.id = A.controlid')->order("cms,ams")->select();

        if($res){
            foreach($res as $v) {
                if (!isset($list[$v['c']])) $list[$v['c']] = array(
                    'control' => $v['c'],
                    'id' => $v['cid'],
                    'name' => $v['cd'],
                    'list' => array(),
                    'sort' => $v['cms'],
                    'icon' => $v['cicon'],
                );

                $list[$v['c']]['list'][$v['a']] = array(
                    'action' => $v['a'],
                    'id' => $v['id'],
                    'name' => $v['describe'],
                    'sort' => $v['ams'],
                    'icon' => $v['aicon'],
                );
            }

            return $list;
        }
        return array();
    }

    public function getName($action='',$control='')
    {
        if (!$action) $action = ACTION_NAME;
        if (!$control) $control = CONTROLLER_NAME;

        $where['A.name'] = $action;
        $where['C.name'] = $control;

        $tname = $this->getTableName();
        $res = $this->field('C.describe as cname,A.describe aname')
            ->alias('A')->where($where)->join($tname . ' C ON C.id = A.controlid')->find();

        if (!$res) $res = array('cname' => strtoupper(CONTROLLER_NAME), 'aname' => strtoupper(ACTION_NAME));

        return $res;
    }

}