<?php
namespace Common\Model;
use Think\Image;
use Think\Model;
/**
 * Class ScProductThumbModel
 * @package Common\Model
 */
class ScProductImgModel extends Model
{

    protected $tableName = 'sc_product_img';

    /**
     * @var array 自动验证
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('utime', 'time', self::MODEL_BOTH, 'function'),
    );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //查询列表
    public function selectList()
    {
        $list = $this->field('id,thumb,imgurl,sort')->order('sort')->select();

        return $list;
    }

    public function selectListByHome()
    {
        $list = $this->field('thumb,imgurl')->order('sort')->select();

        return $list;
    }

    //添加商品预览图，成功返回缩略图
    public function addImg($pid,$img,$thumb,$sort)
    {
        $data['product_id'] = $pid;
        $data['imgurl'] = $img;

        $data['thumb'] = $thumb;
        $data['sort'] = $sort;
        $data['utime'] = time();
        $res = $this->addObj($data);

        return $res;
    }

    public function delImg($pid,$id)
    {
        $where['product_id'] = $pid;
        $where['id'] = $id;
        $obj = $this->findObj($where, 'imgurl,thumb');
        if ($obj) {
            $res = $this->deleteObj($where);
            if($res){
                unlink($obj['imgurl']);
                unlink($obj['thumb']);
            }
            return $res;
        }
        return false;
    }
}