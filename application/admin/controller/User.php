<?php

/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2017/2/13
 * Time: 13:16
 */


namespace app\admin\controller;

use app\admin\model\Member;
use app\common\model\Encriper;

use app\admin\model\Member_level;


use \think\Request;
use \think\Db;

class User extends Common

{

    /**
     * 会员列表
     */

    public function index()

    {

        $size = 10;

        $where = [];

        $where['is_v'] = 0;

        $content = input('username');

        if ($content != '' && $content != null) {

            $where['account'] = [ 'like', $content . '%' ];

            #########################################

        }

        $db = new Member();

        $list = $db->where($where)->order('id desc')->paginate($size, false, [ 'query' => Request::instance()->param() ]);

        $this->assign('list', $list);

        return $this->fetch();

    }

    /*

     * 查看

     */

    public function info()
    {

        $id = input('id');

//        $levelid = input('lid');
//
//        if($levelid == 2||$levelid == 3){
//
//            $plid = 1;
//
//            $list = db('member')->where(['level'=>$plid])->field('id,account,username')->select();
//
//            $this->assign('list',$list);
//
//        }

        if ($id) {

            //当前用户信息

            $info = model('member')->find($id);

            $this->assign('info', $info);

        }

        $level = db("member_level")->field("id,level_name")->select();

        $this->assign('level', $level);


        return $this->fetch();

    }

    /*

     * 添加

     */

    public function add()
    {

        $data = [];

        $count = db('member')->where('account', input('account'))->count();


        if ($count) {

            return json(array ( 'status' => 'n', 'info' => '账号已存在' ));

        }

        $data['account'] = input('account');

        if (Encriper::encrypt(input('login_pwd')) != Encriper::encrypt(input('rlogin_pwd'))) {

            return json(array ( 'status' => 'n', 'info' => '两次密码不匹配' ));

        }

        $data['login_pwd'] = Encriper::encrypt(input('login_pwd'));

        $data['username'] = input('post.username');

        if ($data['secure_pwd'] != $data['rsecure_pwd']) {

            echo json_encode(array ( 'status' => 'n', 'info' => '两次密码不匹配' ));

            die();

        }

        $data['secure_pwd'] = Encriper::encrypt($data['secure_pwd']);


//        $status=array(0,1,2);

//        if(!in_array($data['status'],$status))
//
//        {//判断用户状态是否正确
//
//            return json(array('status'=>'n','info'=>'用户状态有误'));
//
//        }

        $level = db("member_level")->where([ 'id' => input('level') ])->count();

        if ($level == 0) {

            return json([ 'status' => 'n', 'info' => '等级不存在' ]);

        }

        $data['level'] = input("post.level");

        if (is_numeric(input("post.status"))) {

            $data['status'] = input("post.status");

        } else {

            return json([ 'status' => 'n', 'info' => '状态缺失' ]);

        }


        $pname = input("post.pname");

        if (!empty($pname)) {

            $pinfo = db("member")->where([ 'account' => $pname ])->field("id,account,parents")->find();

            if (empty($pinfo)) {

                return json([ 'status' => 'n', 'info' => '填写的上级账号不正确，请填写上级的登录账号' ]);

            }

            $data['pname'] = $pinfo['account'];
            $data['parents'] = $pinfo['parents'] . ',' . $pinfo['id'];
            $data['regtime'] = time();
            $data['regtimeS'] = date("Y-m-d H:i:s", $data['regtime']);
            $data['dailyNum'] = 5;
        }else{
            $data['parents']='';
        }

        $res = db("member")->insertGetId($data);

        if ($res) {

            return json(array ( 'status' => 'y', 'info' => '添加成功' ));

        } else {

            return json(array ( 'status' => 'n', 'info' => '添加失败请重试' ));

        }

    }

    /*

     * 修改

     */

    public function edit()
    {

        $data = input();

        if ($data['login_pwd'] == '') {

            unset($data['login_pwd']);

        } else {

            if ($data['login_pwd'] != $data['rlogin_pwd']) {

                return [ 'status' => 'n', '登录密码不一致' ];

            }

            $data['login_pwd'] = Encriper::encrypt($data['login_pwd']);

        }

        if ($data['secure_pwd'] == '') {

            unset($data['secure_pwd']);

        } else {

            if ($data['secure_pwd'] != $data['rsecure_pwd']) {

                return [ 'status' => 'n', '安全密码不一致' ];

            }

            $data['secure_pwd'] = Encriper::encrypt($data['secure_pwd']);

        }


        $status = array ( 0, 1, 2 );

        if (!in_array($data['status'], $status)) {//判断用户状态是否正确

            return [ 'status' => 'n', 'info' => '用户状态有误' ];

        }
        $pname = input("post.pname");

        if (!empty($pname)) {

            $pinfo = db("member")->where([ 'account' => $pname ])->field("id,account,parents")->find();

            if (empty($pinfo)) {

                return json([ 'status' => 'n', 'info' => '填写的上级账号不正确，请填写上级的登录账号' ]);

            }
            $data['pname'] = $pinfo['account'];
            $data['parents'] = $pinfo['parents'] . ',' . $pinfo['id'];
        }else{
            $data['parents'] = '';
        }

        $field = array ( 'login_pwd', 'secure_pwd', 'status', 'level', 'username', 'pid', 'pname', 'parents' );

        $user = new Member();

        $res = $user->allowField($field)->save($data, [ 'id' => $data['id'] ]);


        if ($res !== false) {

            return [ 'status' => 'y', 'info' => '更改成功' ];

        } else {

            return [ 'status' => 'n', 'info' => '更改失败请重试' ];

        }

    }

    /*

     * 删除

     */

    public function del()
    {

        $id = input('id');

        $res = db('member')->where([ 'id' => $id ])->delete();

        if ($res) {

            return [ 'status' => 'y' ];

        } else {

            return [ 'status' => 'n' ];

        }

    }

    /**
     * ajax 检测用户是否存在
     */

    public function ajaxCheckUser()

    {

        $account = input('param');

        $count = db('member')->where('account', $account)->value("username");


        if ($count) {

            return [ 'status' => 'n', 'info' => $count ];

        } else {

            return [ 'status' => 'y', 'info' => '验证通过' ];

        }

    }

    /*

     * level list

     */

    public function level()

    {

        $size = input('pageSize', 10);

        $db = new Member_level();

        $list = $db->order('id desc')->paginate($size);

        $this->assign('list', $list);

        return $this->fetch('levelIndex');

    }

    /**
     * level info
     */

    public function levelInfo()

    {

        $id = input('id');

        if ($id) {

            //当前用户信息

            $level = new Member_level();

            $info = $level->find($id);

            $this->assign('info', $info);

        }

        return $this->fetch('levelInfo');

    }

    /**
     * level add
     * 添加等级信息
     */

    public function levelAdd()

    {

        $data = input();

        $count = db('member_level')->where('level_name', $data['level_name'])->count();


        if ($count) {

            return [ 'status' => 'n', 'info' => '等级已存在' ];

        }

        $field = array ( 'level_name', 'level_ename', 'level_icon' );

        $level = new Member_level();

        $res = $level->allowField($field)->save($data);


        if ($res) {

            return [ 'status' => 'y', 'info' => '添加成功' ];

        } else {

            return [ 'status' => 'n', 'info' => '添加失败请重试' ];

        }

    }

    /**
     * level edit
     * 修改等级信息
     */

    public function levelEdit()

    {

        $data = input();

        if ($data['level_icon'] == '' || empty($data['level_icon'])) {

            return [ 'status' => 'n', 'info' => '图标必须存在' ];

        }

        if ($data['level_name'] == '' || empty($data['level_name'])) {

            return [ 'status' => 'n', 'info' => '中文名称缺失' ];

        }

        if ($data['level_ename'] == '' || empty($data['level_ename'])) {

            return [ 'status' => 'n', 'info' => '英文名称缺失' ];

        }

        if ($data['level_icon'] != $data['oldPic']) {

            @unlink('.' . $data['oldPic']);

        }

        $filed = [ 'level_name', 'level_ename', 'level_icon' ];

        $level = new Member_level();

        $res = $level->allowField($filed)->save($data, [ 'id' => $data['id'] ]);

        if ($res !== false) {

            return [ 'status' => 'y', 'info' => '更改成功' ];

        } else {

            return [ 'status' => 'n', 'info' => '操作失败' ];

        }

    }


    /**
     * level delete
     * 等级删除
     */

    public function levelDel()

    {

        $id = input('id');

        if (is_numeric($id)) {

            $res = db('member_level')->delete($id);

            if ($res) {

                echo json_encode([ 'status' => 'y' ]);

                die();

            } else {

                echo json_encode([ 'status' => 'n' ]);

                die();

            }

        }

    }

    /**
     * ajax levelName
     */

    public function ajaxCheckLevelName()

    {

        $account = input('param');

        $count = db('member_level')->where('level_name', $account)->count();


        if ($count) {

            echo json_encode(array ( 'status' => 'n', 'info' => '等级已存在' ));

            die();

        } else {

            echo json_encode(array ( 'status' => 'y', 'info' => '验证通过' ));

            die();

        }

    }

    /**
     * 代理申请表
     */
    public function proxy()
    {
        $size = 10;
        $username = input("username");
        $where = [];
        if (!empty($username)) {
            $id = db("member")->where([ 'account' => $username ])->value("id");
            $where['uid'] = [ 'eq', $id ];
        }
        $list = db('proxy')->where($where)->order("status,id desc")->paginate($size, false, [ 'query' => \request()->instance()->param() ]);
        $this->assign("list", $list);
        return $this->fetch();
    }

    /**
     * 审核状态
     */
    function proxy_shenhe()
    {

        if (\request()->isAjax()) {
            $id = input("id");
            $status = intval(input("status"));
            $pinfo = \db("proxy")->find($id);
            if (empty($pinfo)) {
                return showError("找不到要处理的申请");
            }
            if ($status == 1) {
                #通过审核 需将用户表信息更改
                #检查代理时间是否过期
                $dtime = \db("member")->where([ 'id' => $pinfo['uid'] ])->value("d_time");
                if ($dtime > time()) {
                    $upTime = strtotime("+" . $pinfo['month'] . 'month') - time();
                    Db::startTrans();
                    try {
                        //Db::name("member")->where([ 'id' => $pinfo['uid'] ])->setInc('d_time', $upTime);
                        Db::name("member")->where([ 'id' => $pinfo['uid'] ])->update(
                            [ 'd_time' => [ 'exp' => "d_time+" . $upTime ], 'is_d' => 1 ]
                        );
                        Db::name('proxy_log')->insertGetId([
                            'user' => $pinfo['uname'],
                            'changetype' => '+',
                            'num' => $pinfo['month'],
                            'a_user' => $this->user_name,
                            'a_time' => date("Y-m-d H:i:s"),
                            'note' => '代理申请审核通过'
                        ]);
                        Db::name("proxy")->where([ 'id' => $id ])->update([ 'status' => 1 ]);
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        return showError($e->getMessage());
                    }

                } else {
                    Db::startTrans();
                    try {
                        Db::name("member")->where([ 'id' => $pinfo['uid'] ])->update([ 'is_d' => 1,
                            'd_time' => strtotime("+" . $pinfo['month'] . 'month')
                        ]);
                        Db::name('proxy_log')->insertGetId([
                            'user' => $pinfo['uname'],
                            'changetype' => '+',
                            'num' => $pinfo['month'],
                            'a_user' => $this->user_name,
                            'a_time' => date("Y-m-d H:i:s"),
                            'note' => '代理申请审核通过'
                        ]);
                        Db::name("proxy")->where([ 'id' => $id ])->update([ 'status' => 1 ]);
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        return showError($e->getMessage());
                    }
                }

                return showSuccess('操作成功');
            } else if ($status == 2) {

                #通过审核 需将用户表信息更改
                Db::startTrans();
                try {
                    Db::name("member")->where([ 'id' => $pinfo['uid'] ])->update([ 'is_d' => 0 ]);
                    Db::name("proxy")->where([ 'id' => $id ])->update([ 'status' => 2 ]);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    return showError($e->getMessage());
                }
                return showSuccess('操作成功');
            }
        }
    }

    /**
     * 代理时间纠错
     */
    function recharge()
    {
        $id = input("id");

        if (\request()->isAjax()) {
            $username = input('account');
            $type = input("type");
            $num = intval(input("num"));
            if (empty($username) || empty($type) || empty($num)) {
                return json([ 'status' => 'n', '参数错误' ]);
            }
            $upTime = strtotime("+" . $num . 'month') - time();
            Db::startTrans();
            try {
                if ($type == '+') {
                    Db::name("member")->where([ 'account' => $username ])->setInc("d_time", $upTime);
                } else if ($type == '-') {
                    Db::name("member")->where([ 'account' => $username ])->setDec("d_time", $upTime);
                }

                Db::name('proxy_log')->insertGetId([
                    'user' => $username,
                    'changetype' => $type,
                    'num' => $num,
                    'a_user' => $this->user_name,
                    'a_time' => date("Y-m-d H:i:s"),
                    'note' => '系统操作'
                ]);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json([ 'status' => 'n', 'info' => '操作失败' ]);
            }
            return json([ 'status' => 'y', 'info' => '操作成功' ]);
        }
        if (!empty($id)) {
            $this->assign("info", \db("member")->where([ 'id' => $id ])->field("account")->find());
        }
        return $this->fetch();
    }

    /**
     * 代理列表
     * @return string
     */
    function proxy_list()
    {
        $size = 10;
        $where['is_d'] = 1;
        $content = input('username');
        if ($content != '' && $content != null) {
            $where['account'] = [ 'like', $content . '%' ];
            #########################################
        }
        $db = new Member();
        $list = $db->where($where)->order('id desc')->paginate($size, false, [ 'query' => Request::instance()->param() ]);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 充值钱包
     */
    function WalletRecharge()
    {
        if (\request()->isAjax()) {
            $username = strip_tags(input("account"));
            $type = input("type");
            $num = intval(input("num"));
            $changeType = input("change");
            $id = \db("member")->where([ 'account' => $username ])->value("id");


            if (empty($id)) {
                return [ 'status' => 'n', 'info' => '用户不存在' ];
            }
            if ($num <= 0) {
                return [ 'status' => 'n', 'info' => '金额错误' ];
            }
            $column = '';
            switch ($type) {
                case '0':
                    $column = 'wallet';
                    break;
                case '1':
                    $column = "reward";
                    break;
                default:
                    return json([ 'status' => 'n', 'info' => '参数错误' ]);
            }
            Db::startTrans();
            try {
                switch ($changeType) {
                    case '+':
                        Db::name("member")->where([ 'id' => $id ])->setInc($column, $num);
                        break;
                    case '-':
                        Db::name("member")->where([ 'id' => $id ])->setDec($column, $num);
                        break;
                    default:
                        return json([ 'status' => 'n', 'info' => '参数错误' ]);
                }
                meter($username, $num, $changeType, '系统操作', 0, $column, $id);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return [ 'status' => 'n', 'info' => '网络错误' ];
            }
            return [ 'status' => 'y', 'info' => '操作成功' ];
        }
        return $this->fetch();
    }
}