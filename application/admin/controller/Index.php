<?php

/**
 *
 * @file   Index.php
 * @date   2016-8-23 16:03:10
 * @author Zhenxun Du<5552123@qq.com>
 * @version    SVN:$Id:$
 */

namespace app\admin\controller;

use app\admin\model\Menu;
use think\Request;

class Index extends Common
{
    /**
     * 后台首页
     */
    public function main()
    {
        $menu = model('Menu')->getMyMenu($this->user_id, 1);
        $menu = list_to_tree($menu);
        if ($this->user_id == 1) {
            $role = '超级管理员';
        } else {
            $role = db(admin_group_access)->alias(a)
                ->join(config('prefix') . 'admin_group b', 'a.group_id = b.id')
                ->where('uid=' . $this->user_id)->value('name');
        }
        $this->assign('role', $role);
        $this->assign('menu', $menu);
        return $this->fetch('index/main');
    }

    public function welcome()
    {
        $this->assign('info', session("admin"));
        return $this->fetch('index/welcome');
    }

    public function getmessagecount()
    {
        if (Request::instance()->isAjax()) {
            //keep alive myself
            db('admin')->update(['id' => session('user_id'), 'alive_time' => time()]);
            $count = db('mission_sub')->where(['is_read' => 0, 'cuid' => session('user_id')])->count();
            return json($count);
        }
    }
}