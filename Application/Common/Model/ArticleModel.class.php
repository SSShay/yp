<?php
namespace Common\Model;
use Think\Model;
/**
 * Class MenuModel
 * @package Common\Model
 */
class ArticleModel extends Model
{

    protected $tableName = 'article';

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
    //业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //查询文章列表
    public function selectList($where = array(),$page_index = 0,$page_size = 10,$order = 'ctime desc')
    {
        $count = $this->where($where)->count();
        $list = $this->field('id,type,thumb,title,ctime,utime')->where($where)->order($order)
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

    //添加文章
    public function addArticle($uid,$type,$thumb,$title,$brief)
    {
        $data['sendid'] = $uid;
        $data['type'] = $type;
        $data['thumb'] = $thumb;
        $data['title'] = $title;
        $data['brief'] = $brief;
        $res = $this->addObj($data);
        return $res;
    }

    //查询文章列表
    public function selectListByHome($type,$page_index = 0,$page_size = 6,$order = 'ctime desc')
    {
        $where = array('type' => $type);
        $count = $this->where($where)->count();
        $list = $this->field('id,thumb,title,brief,ctime')->where($where)->order($order)
            ->limit($page_index * $page_size, $page_size)
            ->select();

        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['ctime'] = date('Y-m-d H:i', $v['ctime']);
            }
        }else{
            $list = array();
        }

        $result = array('list' => $list, 'count' => ceil($count / $page_size));

        return $result;
    }

    //首页最新文章显示
    public function selectListByIndex($limit = 3,$order = 'ctime desc')
    {
        $list = $this->field('id,thumb,title,brief,ctime')->order($order)->limit($limit)->select();

        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['ctime'] = date('Y-m-d H:i', $v['ctime']);
            }
        }else{
            $list = array();
        }

        return $list;
    }

}