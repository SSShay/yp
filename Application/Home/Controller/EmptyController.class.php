<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1 0001
 * Time: 上午 9:03
 */

namespace Home\Controller;


class EmptyController extends BaseController
{
    public function _initialize()
    {

    }

    public function index()
    {
        $this->_empty();
    }
}