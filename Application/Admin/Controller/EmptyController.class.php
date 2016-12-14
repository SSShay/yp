<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1 0001
 * Time: 上午 9:03
 */
namespace Admin\Controller;
use Think\Controller;

class EmptyController extends Controller
{
    public function _initialize()
    {

    }

    public function index(){
        $this->_404();
    }

    private function _404(){
        echo 404;
    }

    private function _500(){
        echo 500;
    }
}