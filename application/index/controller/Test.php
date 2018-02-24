<?php
/**
 * Created by PhpStorm.
 * User: xxp
 * Date: 2017/11/15
 * Time: 18:15
 */

namespace app\index\controller;


use think\Controller;

class Test extends Controller
{
    function index()
    {
        dump(config("wechat"));
    }
}