<?php
namespace Common\Model;
use Think\Model;
/**
 * Class AreaModel
 * @package Common\Model
 */
class AreaModel extends Model
{

    protected $tableName = 'area';

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
    // 业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * 查询某个级别的地址
     * @param string $topid 上一级地址的id
     * @param int $level 1|2|3级地址
     * @return array
     */
    public function selectArea($topid = '',$level = 1)
    {
        $arr = S('area.' . $level . '_' . $topid);
        if (!$arr) {
            if (!$topid) {
                $where['cityid'] = 0;
                $where['areaid'] = 0;
                $key = 'proviceid';
            } else if ($level == 2) {
                $where['proviceid'] = $topid;
                $where['cityid'] = array('neq', 0);
                $where['areaid'] = 0;
                $key = 'cityid';
            } else {
                $where['cityid'] = $topid;
                $where['areaid'] = array('neq', 0);
                $key = 'areaid';
            }
            $list = $this->field($key . ',name')->where($where)->select();
            $arr = array();
            if (!empty($list)) {
                foreach ($list as $v) {
                    $arr[$v[$key]] = $v['name'];
                }
                S('area.' . $level . '_' . $topid, $arr);
            }

        }


        return $arr;
    }

    //查找所有地址
    public function findDetail($id)
    {
        $res = $this->findObj(array('areaid' => $id), 'name,proviceid,cityid');
        if($res){
            $provice = $this->findObj(array('proviceid' => $res['proviceid'], 'cityid' => 0), 'name');
            $detail['provice'] = $provice['name'];
            $city = $this->findObj(array('cityid' => $res['cityid'], 'areaid' => 0), 'name');
            $detail['city'] = $city['name'];
            $detail['area'] = $res['name'];
            return $detail;
        }

        return null;
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 生成地区js
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const AREA_JS_PATH = './Public/Home/js/area.js';

    public function saveToJS($path = self::AREA_JS_PATH,$name = 0,$list = 1)
    {
        $res = $this->select();
        $arr = array();
        foreach ($res as $v) {
            if ($v['cityid'] == 0) {
                $arr[$v['proviceid']] = array($name => $v['name'], $list => array());
            }
        }

        foreach ($res as $v) {
            if ($v['cityid'] != 0 && $v['areaid'] == 0) {
                $arr[$v['proviceid']][$list][$v['cityid']] = array($name => $v['name'], $list => array());
            }
        }

        foreach ($res as $v) {
            if ($v['areaid'] != 0) {
                $arr[$v['proviceid']][$list][$v['cityid']][$list][$v['areaid']] = $v['name'];
            }
        }

        $fp = fopen($path, "w");
        if ($fp) {
            fwrite($fp, "AREA_LIST=" . $this->str2utf8(json_encode($arr)));
            fclose($fp);
            return true;
        }
        return false;
    }

    const PROVICE_JS_PATH = './Public/Home/js/area.provice.js';
    public function saveProviceToJS($path = self::PROVICE_JS_PATH)
    {
        $where['cityid'] = 0;
        $where['areaid'] = 0;
        $res = $this->field('name,proviceid')->where($where)->select();
        $arr = array();
        foreach ($res as $v) {
            $arr[$v['proviceid']] = $v['name'];
        }

        $fp = fopen($path, "w");
        if ($fp) {
            fwrite($fp, "PROVICE_LIST=" . $this->str2utf8(json_encode($arr)));
            fclose($fp);
            return true;
        }
        return false;
    }

    /*
     * 将字符串去掉转义字符
     */
    protected function str2utf8($str)
    {
        $str = str_replace("\\/", "/", $str);
        $search = "#\\\u([0-9a-f]{0,4})#ie";
        if (strpos(strtoupper(PHP_OS), 'WIN') === false) {
            $replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
        } else {
            $replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
        }
        return preg_replace($search, $replace, $str);
    }

    /*
     *
     public function save(){
        $area_obj = new AreaModel();
        $area_obj->saveToJS();
    }
     */
}