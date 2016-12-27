<?php
namespace Common\Model;
use Think\Model;
/**
 * Class MenuModel
 * @package Common\Model
 */
class MenuModel extends Model
{

    protected $tableName = 'menu';

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
    public function selectList()
    {
        //F：一级菜单
        //S：二级菜单

        $where['topid'] = 0;
        $flist = $this->field('id,name,link,sort,display,sitemap')->where($where)->order('sort')->select();
        if ($flist) {
            foreach ($flist as $k => $v) {
                unset($where);
                $where['topid'] = $v['id'];
                $slist = $this->field('id,name,link,sort,display,sitemap')->where($where)->order('sort')->select();
                $flist[$k]['list'] = $slist;
            }
            return $flist;
        }
        return array();
    }

    public function addMenu($name,$sort,$link,$topid,$sitemap)
    {
        $data['name'] = $name;
        $data['sort'] = $sort;
        $data['link'] = $link;
        $data['topid'] = $topid;
        $data['sitemap'] = $sitemap;
        $data['utime'] = time();

        if ($this->create($data)) {
            $res = $this->add();
            return $res;
        }
        return false;
    }

    public function delMenu($id)
    {
        $where['id'] = $id;
        $where['topid'] = $id;
        $where['_logic'] = 'OR';
        $res = $this->where($where)->delete();

        return !!$res;
    }

    public function selectListByHome()
    {
        //F：一级菜单
        //S：二级菜单

        $where['topid'] = 0;
        $where['display'] = 1;
        $flist = $this->field('id,name,link,sitemap')->where($where)->order('sort')->select();
        if ($flist) {
            foreach ($flist as $k => $v) {
                if (empty($v['link'])) {
                    $flist[$k]['link'] = "javascript:;";
                } elseif (strpos($v['link'], 'http://') !== 0) {
                    $flist[$k]['link'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . $v['link'];
                }
                unset($where);
                $where['topid'] = $v['id'];
                //$where['display'] = 1;
                $slist = $this->field('name,link,sitemap')->where($where)->order('sort')->select();
                $flist[$k]['list'] = $slist;
            }
            return $flist;
        }
        return array();
    }

}