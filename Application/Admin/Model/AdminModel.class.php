<?php
namespace Admin\Model;
use Think\Model;
/**
 * 管理员模型
 * Class AdminModel
 * @package Common\Model
 */
class AdminModel extends Model
{

    protected $tableName = 'admin';

    /**
     * @var array 自动验证
     */
    protected $_validate = array(
        array('loginuser','require','账号不能为空！'),
        array('password','require','密码不能为空！'),
    );

    /**
     * @var array
     * 自动完成   新增时
     */
    protected $_auto = array(
        array('ctime','time',self::MODEL_INSERT,'function'),
        array('utime','time',self::MODEL_UPDATE,'function'),
    );


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //业务逻辑
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    const ip_null = 'unknown';
    //获取登录者的ip
    private function get_client_ip()
    {
        $ip = getenv("HTTP_CLIENT_IP");
        if (!$ip || !strcasecmp($ip, self::ip_null)){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
            if (!$ip || !strcasecmp($ip, self::ip_null)){
                $ip = getenv("REMOTE_ADDR");
                if (!$ip || !strcasecmp($ip, self::ip_null)){
                    $ip = $_SERVER['REMOTE_ADDR'];
                    if (!$ip || !strcasecmp($ip, self::ip_null)){
                        $ip = '';
                    }
                }
            }
        }
        return $ip;
    }

    //登录
    public function login($user, $pwd)
    {
        $res = $this->findObj(array('loginuser' => $user), 'id,password,name,ip,ctime,utime,permissions');
        if ($res) {
            if(!$res['ip']) $res['ip'] = '未知';
            if ($res['password'] == $this->encryption($pwd)) {
                $permissions_obj = new AdminPermissionsModel();
                $permissions = $permissions_obj->selectList($res['permissions']);

                $this->where(array('id' => $res['id']))//
                    ->data(array('ip' => $this->get_client_ip(),'utime' => NOW_TIME))//
                    ->save();

                $res['loginuser'] = $user;
                unset($res['password']);
                unset($res['permissions']);
                session('admin', $res);
                session('permissions', $permissions);
                return true;
            } else $this->error = '你输入的密码错误，请核对后再试！';

        } else $this->error = '你输入的管理员账号不存在，请核对后再试！';

        return false;
    }

    //退出
    public function logout()
    {
        session('admin', null);
        session('permissions', null);
    }

    //密码加密
    private static function encryption($password)
    {
        return md5(crypt(sha1(md5($password . 'panyard', true) . 'admin', true), 'panyard') . 'salt');
        //  return $password;
    }

    //查询
    public function selectList($where = array(), $page_index = 0, $page_size = 10, $order = 'ctime desc')
    {
        $count = $this->where($where)->count();
        $list = $this->field('id,loginuser,password,name,ip,ctime,utime,permissions')->where($where)->order($order)
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
}