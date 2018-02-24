<?php

/**
 *  后台继承类
 * @file   Admin.php
 * @date   2017年5月24日10:06:19
 * edit 2
 */

namespace app\admin\controller;

use think\Request;
use think\Loader;

class Admin extends Common
{

    public function index()
    {
        $size = 10;
        $where = array ();
        if ($group_id = input('group_id')) {
            $where['t2.group_id'] = $group_id;

        }
        $username = input('get.username');
        if (!empty($username)) {
            $where['username'] = [ 'like', $username . '%' ];
        }
        if (session('user_id') == 1) {
            $lists = db('admin')->alias('t1')->field('t1.*')
                ->where($where)
                ->join(config('database.prefix') . 'admin_group_access t2', 't1.id=t2.uid', 'left')
                ->group('t1.id')
                ->order('t1.id desc')
                ->paginate($size, false, [ 'query' => Request::instance()->param() ]);
        } else {
            $where['parent_id'] = [ 'eq', session('user_id') ];
            $lists = db('admin')->alias('t1')->field('t1.*')
                ->where($where)
                ->join(config('database.prefix') . 'admin_group_access t2', 't1.id=t2.uid', 'left')
                ->group('t1.id')
                ->order('t1.id desc')
                ->paginate($size, false, [ 'query' => Request::instance()->param() ]);
        }
//        foreach ($lists as $k => $v) {
//            //取出组名
//            $lists[$k]['groups'] = '';
//            $groups = Loader::model('Admin')->getUserGroups($v['id']);
//            if ($groups) {
//                $tmp = db('admin_group')->field('name')->where('id', 'in', $groups)->select();
//
//                foreach ($tmp as $vv) {
//                    $lists[$k]['groups'] .= $vv['name'] . ',';
//                }
//                $lists[$k]['groups'] = trim($lists[$k]['groups'], ',');
//            }
//        }

        //echo $size;die;
        $this->assign('list', $lists);
        return $this->fetch();
    }

    /*
     * 查看
     */

    public function info()
    {
        $id = input('id');
        if ($id) {
            //当前用户信息
            $info = model('Admin')->getInfo($id);
            $info['userGroups'] = Loader::model('Admin')->getUserGroups($id);
            $this->assign('info', $info);
        }

        //所有组信息
        $groups = model('AdminGroup')->getGroups();

        $this->assign('groups', $groups);
        return $this->fetch();
    }

    /**
     * ajax check
     */
    public function checkusername()
    {
        if (Request::instance()->isAjax()) {
            $data = input('param');
            $count = db('admin')->where('username', $data)->count();

            if ($count) {
                return json([ 'status' => 'n', 'info' => '用户存在' ]);
            } else {
                return json([ 'status' => 'y' ]);
            }
        }
    }

    /*
     * 添加
     */

    public function add()
    {
        $data = input();
        $count = db('admin')->where('username', $data['username'])->count();
        if ($count) {
            return json([ 'status' => 'n', 'info' => '用户名已存在' ]);
        }

        if ($data['password'] != $data['rpassword']) {
            return json([ 'status' => 'n', 'info' => '两次密码不一致' ]);
        }

        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (!preg_match($pattern, $data['email'])) {
            return json([ 'status' => 'n', 'info' => '邮箱格式有误' ]);
        }

        $data['encrypt'] = getRandStr(6);
        $data['password'] = md5($data['password'] . md5($data['encrypt']));

        $res = model('Admin')->allowField(true)->save($data);

        if ($res) {
            if (isset($data['groups'])) {
                $uid = model('Admin')->id;
                foreach ($data['groups'] as $v) {
                    db('admin_group_access')->insert([ 'uid' => $uid, 'group_id' => $v ]);
                }
            }
            return json([ 'status' => 'y', 'info' => '操作成功' ]);
        } else {
            return json([ 'status' => 'n', 'info' => '操作失败' ]);
        }
    }

    /*
     * 修改
     */

    public function edit()
    {
        $data = input();
        db('admin_group_access')->where([ 'uid' => $data['id'] ])->delete();

        if (isset($data['groups'])) {
            foreach ($data['groups'] as $v) {
                db('admin_group_access')->insert([ 'uid' => $data['id'], 'group_id' => $v ]);
            }
        }


        if (!$data['password']) {
            unset($data['password']);
        } else {
            if ($data['password'] != $data['rpassword']) {
                return json([ 'status' => 'n', 'info' => '两次密码不一致' ]);
            }
            $enc = Loader::model('Admin')->where('id=' . $data['id'])->column('encrypt');
            $data['password'] = md5($data['password'] . md5($enc[0]));
        }

        $res = Loader::model('Admin')->editInfo(2, $data['id'], $data);

        if ($res) {
            return json([ 'status' => 'y', 'info' => '操作成功' ]);
        } else {
            return json([ 'status' => 'n', 'info' => '操作失败' ]);
        }
    }

    /*
     * 删除
     */

    public function del()
    {
        $id = input('id');
        $res = db('admin')->where([ 'id' => $id ])->delete();
        if ($res) {
            db('admin_group_access')->where([ 'uid' => $id ])->delete();
            return json([ 'status' => 'y' ]);
        } else {
            return json([ 'status' => 'n' ]);
        }
    }

    /**
     * 修改个人信息
     */
    public function public_edit_info()
    {
        $data = input();
        if ($this->request->isAjax()) {
            $needLogin = false;
            if (!$data['password']) {
                unset($data['password']);
            } else {
                if ($data['password'] != $data['rpassword']) {
                    return json([ 'status' => 'n', 'info' => '两次密码不一致!' ]);
                }
                $enc = Loader::model('Admin')->where([ 'id' => $this->user_id ])->column('encrypt');
                $data['password'] = md5($data['password'] . md5($enc[0]));
                $needLogin = true;
            }
            $res = Loader::model('Admin')->editInfo(2, $this->user_id, $data);
            if ($res) {
                return json([ 'status' => 'y', 'info' => '修改成功', 'need_login' => $needLogin ]);
            } else {
                return json([ 'status' => 'n', 'info' => '修改失败' ]);
            }
        } else {
            $info = db('admin')->where('id', $this->user_id)->find();
            $this->assign('info', $info);
            return $this->fetch();
        }
    }
}
