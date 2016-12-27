<?php
namespace Common\Model;
use Think\Model;
/**
 * Class BannerModel
 * @package Common\Model
 */
class BannerModel extends Model
{

    const T_index = 0;      //首页轮播图
    const T_article = 1;    //文章页面轮播图
    const T_mall = 2;       //官网商城轮播图

    protected $tableName = 'banner';

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
    /**
     * 查询显示的轮播图
     * @param int $type 0：首页轮播图，1：首页轮播图，2：首页轮播图
     * @return mixed
     */
    public function selectList($type = 0)
    {
        $where['type'] = $type;
        $list = $this->field('id,imgurl,imgalt,link,title,content,sort')->where($where)->order('sort')->select();
        if(!$list) $list = array();

        return $list;
    }

    /**
     * 查询显示的轮播图
     * @param int $type 0：首页轮播图，1：首页轮播图，2：首页轮播图
     * @return mixed
     */
    public function selectListByHome($type = 0)
    {
        $where['type'] = $type;
        $where['display'] = 1;
        //$where['display'] = 1;
        $list = $this->field('imgurl,imgalt,link,title,content')->where($where)->order('sort')->select();

        return $list;
    }

    public function setBanner($id,$url)
    {
        $where['id'] = $id;
        $obj = $this->findObj($where, 'imgurl');
        if ($obj) {
            $data['imgurl'] = $url;
            $data['utime'] = time();
            $res = $this->setObj($where, $data);
            if ($res) {
                unlink($obj['imgurl']);
            }
            return $res;
        }
        return false;
    }

    public function addBanner($url,$sort,$type = 0)
    {
        $data['imgurl'] = $url;
        $data['sort'] = $sort;
        $data['type'] = $type;
        $data['utime'] = time();
        $res = $this->addObj($data);
        return $res;
    }

    public function delBanner($id)
    {
        $where['id'] = $id;
        $obj = $this->findObj($where, 'imgurl');
        if ($obj) {
            $res = $this->deleteObj($where);
            if($res){
                unlink($obj['imgurl']);
            }
            return $res;
        }

        return false;
    }

}