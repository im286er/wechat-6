<?php

/**
 *  登陆页
 * @file   Login.php
 * @date   2016-8-23 19:52:46
 * @author Zhenxun Du<5552123@qq.com>
 * @version    SVN:$Id:$
 */

namespace app\admin\controller;

use think\Controller;
use think\Loader;
use think\Request;

class Login extends Controller
{

    /**
     * 登入
     */
    public function index($username = '', $password = '', $code = '', $online = 0)
    {
        if (request()->instance()->isAjax()) {
            $validate = [
                'username|账号' => "require|alphaNum",
                'password|密码' => "require|alphaDash",
                'code|验证码' => "require|length:3"
            ];
            $cres = $this->validate(request()->param(), $validate);
            if ($cres !== true) {
                return json(['status' => 'n', 'info' => $cres]);
            }

            if (!captcha_check($code, 'admin_login', config("captcha"))) {
                return json(['status' => 'n', 'info' => '验证码错误']);
            }
            $info = model('Admin')->where('username', $username)->find()->toArray();

            if (!$info) {
                return json(['status' => 'n', 'info' => '用户不存在']);
            }
            if (md5($password . md5($info['encrypt'])) != $info['password']) {
                return json(['status' => 'n', 'info' => '密码不正确']);
            } else {
                if ($info['status'] == 0) {
                    return json(['status' => 'n', 'info' => '账户被禁用']);
                }
                session('user_name', $info['username']);
                session('user_id', $info['id']);
                session("admin", $info);
                if ($online) {
                    cookie('user_name', encry_code($info['username']));
                    cookie('user_id', encry_code($info['id']));
                    cookie("user_key", encry_code($info['username'] . "|" . $info['password']));
                }
                //记录登录信息
                Loader::model('Admin')->editInfo(1, $info['id']);
                return json(['status' => 'y', 'url' => url('index/main')]);
            }
        } else {
            if (session('user_name')) {
                $this->redirect('admin/index/main');
            }

if (cookie('user_name')) {
    $username = encry_code(cookie('user_name'), 'DECODE');
    $password = explode('|', encry_code(cookie('user_key'), 'DECODE'))[1];
    $validate = [
        'username|账号' => "require|alphaNum",
        'password|密码' => "require|alphaDash"
    ];
    $cres = $this->validate(['username' => $username, 'password' => $password], $validate);
    if ($cres !== true) {
        $this->redirect("login/logout");
    }
    $info = db('admin')->field('id,username,password,encrypt,status')->where('username', $username)->find();
    if (empty($info) || $password != $info['password'] || $info['status'] == 0) {
        #密码错误 用户不存在  用户禁用
        $this->redirect("login/logout");
    } else {
        session('user_name', $info['username']);
        session('user_id', $info['id']);
        Loader::model('Admin')->editInfo(1, $info['id']);
        $this->redirect("index/main");
    }
}
return $this->fetch('login');
}
}

/**
 * 登出
 */
public
function logout()
{
    session('user_name', null);
    session('user_id', null);
    cookie('user_name', null);
    cookie('user_id', null);
    cookie('user_key', null);
    return $this->redirect('Login/index');
}

public
function admin_cap($id = 'admin_login', $config = [])
{
    $config = config("captcha");
    $captcha = new \think\captcha\Captcha($config);
    return $captcha->entry($id);
}
}
