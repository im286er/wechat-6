<?php

/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2017-06-08
 * Time: 14:05:41
 */


namespace app\admin\controller;


use \think\Request;
use \think\Db;

class Market extends Common

{

    /**
     * 流水列表
     */

    public function index()

    {

        $size = 10;

        $where = '';

        $content = input('username');

        if ($content != '' && $content != null) {

            $where = "user like '" . $content . "%'";

            $this->assign('key', $content);

            #########################################

        }

        $list = db("market")->where($where)->order('id desc')->paginate($size, false, [ 'query' => Request::instance()->param() ]);

        $this->assign('list', $list);

        return $this->fetch();

    }

    /*

     * 驳回提现

     */

    public function reject()
    {
        if (request()->isAjax()) {
            $id = input('id');
            $note = input("note");
            $res = db("market")->where([ 'id' => $id ])->update([ 'status' => 2, 'note' => $note, 'c_date' => time() ]);
            if ($res) {
                return showSuccess('操作成功');
            } else {
                return showError("操作失败");
            }
        }
    }


    /**
     * 提现确认打款
     */
    public function ensure()
    {
        if (request()->isAjax()) {
            $id = input('id');
            $res = db("market")->where([ 'id' => $id ])->update([ 'status' => 1, 'c_date' => time() ]);
            if ($res) {
                return showSuccess('操作成功');
            } else {
                return showError("操作失败");
            }
        }
    }

    /**
     * 游戏流水
     */
    public function checklist()
    {
        $size = 10;
        $where = [];
        $username = input("username");
        if (!empty($username)) {
            $where['user'] = [ 'eq', $username ];
        }

        $list = Db::name("stream")->where($where)->order("id desc")->paginate($size, false, [ 'query' => request()->instance()->param() ]);
        $this->assign("list", $list);
        return $this->fetch();
    }

    /**
     * 充值申请
     * @return [type] [description]
     */
    public function recharge()
    {
        $map = [];
        $size = 10;
        if (request()->isPost()) {

        }
        $list = Db::name("recharge")->where($map)->order("date desc")->paginate($size, false, [ 'query' => request()->instance()->param() ]);
        $this->assign("list", $list);
        $this->assign("page", $list->render());
        return $this->fetch();
    }

    /**
     * 充值确认
     * @return [type] [description]
     */
    function ensure_recharge()
    {
        if (request()->isAjax()) {
            $id = input('id');
            $info = Db::name("recharge")->where([ 'id' => $id ])->find();
            if ($info['status'] == 1) {
                return showError("已经确认");
            }
            Db::startTrans();
            try {
                Db::name("recharge")->where([ 'id' => $id ])->update([ 'status' => 1, 'c_date' => date("Y-m-d H:i:s") ]);
                Db::name("member")->where([ 'id' => $info['uid'] ])->setInc('wallet', $info['money']);
                meter($info['uname'], $info['money'], '+', '用户充值', 9, 'wallet', $info['uid']);
                Db::commit();
                return showSuccess('操作成功');
            } catch (\Exception $e) {
                Db::rollback();
                return showError("操作失败");
            }
        }
    }

}