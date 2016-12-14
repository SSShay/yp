<?php
namespace Common\Model;
use Think\Model;
/**
 * Class VideoModel
 * @package Common\Model
 */
class VideoModel extends Model
{

    protected $tableName = 'video';

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
        $list = $this->field('id,name,thumb,url,sort')->order('sort')->select();
        return $list;
    }

    public function selectListByHome()
    {
        $where['display'] = 1;
        //$where['display'] = 1;
        $list = $this->field('name,thumb,url')->where($where)->order('sort')->select();
        return $list;
    }

    public function setVideo($id,$thumb = '',$url = '')
    {
        $where['id'] = $id;
        $obj = $this->findObj($where, 'thumb,url');
        if ($obj) {
            if ($thumb) {
                unlink($obj['thumb']);
                $data['thumb'] = $thumb;
            }

            if ($url) {
                unlink($obj['url']);
                $data['url'] = $thumb;
            }
            if (empty($data)) return false;
            $data['utime'] = time();
            $res = $this->setObj($where, $data);
            if ($res) {
                if ($thumb) {
                    unlink($obj['thumb']);
                }

                if ($url) {
                    unlink($obj['url']);
                }
            }
            return $res;
        }
        return false;
    }

    public function addVideo($name,$thumb,$url,$sort)
    {
        $data['name'] = $name;
        $data['thumb'] = $thumb;
        $data['url'] = $url;
        $data['sort'] = $sort;
        $data['utime'] = time();
        $res = $this->addObj($data);
        return $res;
    }

    public function delVideo($id)
    {
        $where['id'] = $id;
        $obj = $this->findObj($where, 'thumb,url');
        if ($obj) {
            $res = $this->deleteObj($where);
            if($res){
                unlink($res['thumb']);
                unlink($res['url']);
            }
            return $res;
        }

        return false;
    }

    //获取第一个视频的缩略图
    public function getFirst()
    {
        $where['display'] = 1;
        //$where['display'] = 1;
        $list = $this->field('name,thumb')->where($where)->order('sort')->find();
        return $list;
    }

}