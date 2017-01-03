<?php

namespace Admin\Controller;

use Common\Model\ArticleModel;
use Common\Model\VideoModel;

class ContentController extends BaseController
{
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  文章管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const page_key = 'article-page-index';
    //文章管理主页面
    public function article()
    {
        $this->U_check_permissions();

        $this->page_index = session(self::page_key);
        if(!$this->page_index) $this->page_index = 0;
        $this->display('article');
    }

    //文章管理列表
    public function article_list()
    {
        $this->U_check_permissions('article');

        $p = I('get.p');
        session(self::page_key, $p);
        $article_obj = new ArticleModel();
        $res = $article_obj->selectList('', $p);
        echo json_encode($res);
    }

    //文章缩略图片上传
    public function article_thumb_upload()
    {
        $this->U_check_permissions('article');

        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 32768,//最大上传文件大小不超过32K
                'rootPath' => 'Uploads',
                'savePath' => '/article/thumb/',
                'saveName' => date('YmdHis') . '_' . rand(1000, 9999),
                'autoSub' => false,
                'exts' => array('jpg', 'png'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['img'];
                //返回文件地址和名给JS作回调用
                $img = '/Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                $oldimg = I('post.img');
                if ($oldimg) unlink($oldimg);
                $result['img'] = $img;
            } else {
                $result['error'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['error'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    //文章内容图片上传
    //参照 plugin-kindeditor/php/upload_json.php 上传接口修改
    public function article_img_upload()
    {
        $this->U_check_permissions('article');

        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 262144,//最大上传文件大小不超过256K
                'rootPath' => 'Uploads',
                'savePath' => '/article/',
                'saveName' => array('uniqid'),
                'exts' => array('jpg','png'),
                'subName' => array('date', 'Y-m-d'),
            );
            $result['error'] = 1;
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['imgFile'];
                //返回文件地址和名给JS作回调用
                $img = '/Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                /*$oldimg = I('post.img');
                if ($oldimg) unlink($oldimg);*/
                $result['error'] = 0;
                $result['url'] = $img;
            } else {
                $result['message'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['message'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    //文章内容图片删除
    public function article_img_del()
    {
        $this->U_check_permissions('article');

        $img = I('post.img');
        unlink($img);
    }

    //文章添加
    public function article_add()
    {
        if (IS_POST) {
            $this->U_check_permissions('article');

            $type = I('post.type');
            $article_obj = new ArticleModel();
            $html = $_POST['html'];
            $brief = mb_substr(strip_tags($html), 0, 200, 'utf-8');
            $id = $article_obj->addArticle($this->U_info('id'), $type, I('post.thumb'), I('post.alt'), I('post.title'), $brief);
            if ($id) {
                $res = $this->save_article($type, $id, $html);
                if ($res) $return['success'] = true;
                else {
                    $return['error'] = '文章保存失败...';
                    $return['code'] = 100;
                    $article_obj->deleteObj(array('id' => $id));
                }
            } else {
                $return['error'] = '文章入库失败...';
                $return['code'] = 0;
            }
            echo json_encode($return);
        }
    }

    //文章修改页面
    public function article_mod()
    {
        $this->U_check_permissions('article');

        $id = I('get.id');
        $article_obj = new ArticleModel();
        $article = $article_obj->findObj(array('id' => $id), 'type,thumb,alt,title,brief');
        if (!$article) $this->error('找不到文章...');
        $this->id = $id;
        $this->article = $article;
        $filename = $this->get_article_url($article['type'], $id);  //要写入的文件名字
        $content = file_get_contents($filename);
        $this->content = str_replace(array("\r\n", "\r", "\n"), "", $content);
        $this->display('article_mod');
    }

    public function article_save()
    {
        if(IS_POST){
            $this->U_check_permissions('article');

            $id = I('get.id');
            $article_obj = new ArticleModel();
            $mod = I('post.mod');
            if(!empty($mod)) {
                if(isset($_POST['html'])){
                    $mod['brief'] = mb_substr(strip_tags($_POST['html']), 0, 200, 'utf-8');
                }
                $res = $article_obj->setObj(array('id' => $id), $mod);
                if ($res) {
                    $type = I('post.type');
                    $newtype = $mod['type'];
                    if($newtype){
                        $oldfile = $this->get_article_url($type, $id);  //要写入的文件名字
                        unlink($oldfile);
                        $type = $newtype;
                    }

                    $res = $this->save_article($type, $id,$_POST['html']);
                    if($res){
                        $return['success'] = true;
                    }else{
                        $return['error'] = '文章内容修改失败！';
                    }
                }
                else{
                    $return['error'] = '文章属性修改失败！';
                }
            }else{
                $return['error'] = '文章没有修改，无需保存！';
            }

            echo json_encode($return);
        }
    }

    //文章删除
    public function article_del()
    {
        if (IS_POST) {
            $this->U_check_permissions('article');

            $id = I('post.id');
            $article_obj = new ArticleModel();
            $article = $article_obj->findObj(array('id' => $id), 'type,thumb');
            if ($article) {
                if ($article['thumb']) unlink($article['thumb']);
                $filename = $this->get_article_url($article['type'], $id);  //要写入的文件名字
                unlink($filename);

                $res = $article_obj->deleteObj(array('id' => $id));
                if ($res) {
                    $return['success'] = true;
                } else {
                    $return['error'] = $article_obj->getError();
                }
            } else {
                $return['error'] = '找不到文章...，请刷新重试！';
            }

            echo json_encode($return);
        }
    }


    const article_path = 'Application/Home/View/Default/News/';
    private function save_article($type,$id,$html)
    {
        $filename = $this->get_article_url($type,$id);  //要写入的文件名字
        $fh = fopen($filename, "w");
        if ($fh) {
            fwrite($fh, $html);
            fclose($fh);
            return true;
        } else {
            return false;
        }
    }

    private function get_article_url($type,$id)
    {
        $tlist = array('CompanyNews', 'IndustryDynamics', 'PolicySupport');
        $filename = self::article_path . $tlist[$type] . '/' . $id . '.html';  //要写入的文件名字
        return $filename;
    }

    public function article_banner()
    {
        $this->display('article_banner');
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  视频管理
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function video()
    {
        $this->U_check_permissions();
        if(IS_AJAX){
            dump($_POST);
        }else{
            $banner_obj = new VideoModel();
            $video = $banner_obj->selectList();
            $this->video = json_encode($video);

            $this->display('video');
        }
    }

    //视频缩略图上传
    public function video_thumb_upload()
    {
        $this->U_check_permissions('video');

        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(
                'maxSize' => 262144,//最大上传文件大小不超过256K
                'rootPath' => 'Uploads',
                'savePath' => '/video/thumb/',
                'saveName' => array('uniqid'),
                'exts' => array('jpg','png'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $imginfo = $images['img'];
                //返回文件地址和名给JS作回调用
                $img = '/Uploads' . $imginfo['savepath'] . $imginfo['savename'];
                $oldimg = I('post.img');
                if ($oldimg) unlink($oldimg);
                $result['img'] = $img;
            } else {
                $result['error'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['error'] = "图片上传失败！";
        }
        echo json_encode($result);
    }

    /**
     * 在php.ini文件中查找以下内容：
     * “max_execution_time =" 数值改为 1200
     * “max_input_time =  ”   数值改为 1200
     * “memory_limit =   ”    数值改为 256
     * “post_max_size = ”   需要上传多大的文件将数值改为多大
     * “upload_max_filesize = ” 跟上面这个数值一样就可以了
     * 这样设置应该可以确保文件大小上传没有问题。
     *
     * 第二步，解决执行时间错误。“Maximum execution time of 300seconds”，
     * 这个问题要找到config.inc.php这个文件，文件路径D:\wamp\apps\phpmyadmin2.11.6；
     * 相信能看懂。找到文件后查找$cfg['ExecTimeLimit'] = 300;
     * 把300改为0就是不限制时间，当然可根据需要改成30000000都是随意的
     */
    public function video_upload()
    {
        $this->U_check_permissions('video');

        if (!empty($_FILES)) {
            //文件上传设置
            $config = array(
                'maxSize' => 104857600,//最大上传文件大小不超过 100M
                'rootPath' => 'Uploads',
                'savePath' => '/video/',
                'saveName' => array('uniqid'),
                'exts' => array('mp4','flv','swf'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $file = $upload->upload();
            //判断是否有图
            if ($file) {
                //返回文件地址和名给JS作回调用
                $video = '/Uploads' . $file['file']['savepath'] . $file['file']['savename'];
                $result['video'] = $video;
            } else {
                $result['error'] = $upload->getError();//获取失败信息
            }
        } else {
            $result['error'] = "视频上传失败，请联系管理员检查服务器设置！";
        }
        echo json_encode($result);

    }

    //视频缩略图上传
    public function video_add()
    {
        $this->U_check_permissions('video');
        $menu_obj = new VideoModel();
        $res = $menu_obj->addVideo(I('post.name'),I('post.thumb'),I('post.url'),I('post.sort'));
        echo json_encode(array('id' => $res));
    }

    //视频缩略图上传
    public function video_del()
    {
        $this->U_check_permissions('video');
        if (IS_AJAX) {
            $banner_obj = new VideoModel();
            $res = $banner_obj->delVideo(I('post.id'));
            if ($res) {
                $r['success'] = true;
            } else {
                $r['error'] = $banner_obj->getError();
            }
            echo json_encode($r);
        }
    }

    //视频属性修改
    public function video_mod()
    {
        $this->U_check_permissions('video');

        if (IS_POST) {
            $change = I('post.change');
            $video_obj = new VideoModel();
            $return['fail'] = 0;
            $return['count'] = count($change);
            foreach ($change as $v) {
                $where['id'] = $v['id'];
                $v['utime'] = time();
                unset($v['id']);
                $res = $video_obj->setObj($where, $v);
                if (!$res) $return['fail']++;
            }
            echo json_encode($return);
        }
    }

}