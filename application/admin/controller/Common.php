<?php

/**
 * 后台公共文件
 * @file   Common.php
 * @date   2016-8-24 18:28:34
 * @author Zhenxun Du<5552123@qq.com>
 * @version    SVN:$Id:$
 */

namespace app\admin\controller;

use think\Controller;
use think\db;

class Common extends Controller
{

    protected $user_id;
    protected $user_name;
    protected $accessToken;

    public function __construct(\think\Request $request = null)
    {

        parent::__construct($request);

        if (!session('user_id')) {

            $this->error('请登陆', 'login/index', '', 0);
        }

        $this->user_id = session('user_id');
        $this->user_name = session('user_name');
        $this->accessToken = config('TOKEN');
        //权限检查
        if (!$this->_checkAuthor($this->user_id)) {
            $this->error('你无权限操作');
        }

        //记录日志
        $this->_addLog();
    }

    /**
     * 权限检查
     */
    private function _checkAuthor($user_id)
    {

        if (!$user_id) {
            return false;
        }
        if ($user_id == 1) {
            return true;
        }
        $c = strtolower(request()->controller());
        $a = strtolower(request()->action());

        if (preg_match('/^public_/', $a)) {
            return true;
        }
        if ($c == 'index' && $a == 'index') {
            return true;
        }
        if (in_array($a, config('notAuthaccess'))) {
            return true;
        }
        $menu = model('Menu')->getMyMenu($user_id);
        foreach ($menu as $k => $v) {
            if (strtolower($v['c']) == $c && strtolower($v['a']) == $a) {
                return true;
            }
        }
        return false;
    }

    /**
     * 记录日志
     */
    private function _addLog()
    {

        $data = array();
        $data['querystring'] = request()->query() ? '?' . request()->query() : '';
        $data['m'] = request()->module();
        $data['c'] = request()->controller();
        $data['a'] = request()->action();
        $data['userid'] = $this->user_id;
        $data['username'] = $this->user_name;
        $data['ip'] = ip2long(request()->ip());
        $data['time'] = date('Y-m-d H:i:s');
        //$arr = array('Index/index','Log/index','Menu/index');
        $arr = [];
        if (!in_array($data['c'] . '/' . $data['a'], $arr)) {
            db('admin_log')->insert($data);
        }
    }

    /**
     * upload img
     */
    public function uploadImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('imgFile');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $path = input('path');
        $info = $file->validate(['size'=>2048*1024*1024,'ext'=>'jpg,png,gif'])->rule("date")->move(ROOT_PATH . 'public' . DS . 'uploads'.DS. $path);
        if ($info) {
            return json(['error' => 0, 'url' => str_replace('\\','/',DS. 'uploads'.DS. $path.DS.$info->getSaveName())]);
        } else {
            // 上传失败获取错误信息
            return json(["error"=>1,'message'=>$file->getError()]);
        }
    }

    /**
     * change  status
     */
    public function changeStatus()
    {
        $data = input();
        $db = Db::name($data['table']);
        $value = $db->find($data['id']);
        if (!empty($value)) {
            if ($value[$data['column']] == 1) {
                $db->where('id=' . $data['id'])->update([$data['column'] => 0]);
                echo 0;
                die();
            } else {
                $db->where('id=' . $data['id'])->update([$data['column'] => 1]);
                echo 1;
                die();
            }
        }
    }
}
