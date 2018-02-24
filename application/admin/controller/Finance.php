<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use application\admin\model\Finance;

class Finance extends Common
{
    /**
     * 已添加业绩 列表显示
     * @return mixed
     */
    public function info()
    {
        $size = 10;
        $where['uid'] = ['eq', session('user_id')];
        $startTime = input('startTime');
        $endTime = input('endTime');
        if (!empty($startTime)) {
            $where['pv_time'] = ['gt', strtotime($startTime)-60*60*24];
        }
        if (!empty($endTime)) {
            $where['pv_time'] = ['lt', strtotime($endTime)];
        }
        $list = Db::name('finance')->field('id,pv,pv_a,member_num,member_num_a,margin,unit_with,pv_time')->where($where)
            ->paginate($size, false, ['query' => Request::instance()->param()]);
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    /**
     * 业绩详细页
     * @return mixed
     */
    public function detail()
    {
        $id = input('id');
        if (is_numeric($id)) {
            $info = Db::name('finance')->find($id);
            $this->assign('info', $info);
        }
        return $this->fetch('finance/detail');
    }

    /**
     * 业绩添加
     * @return \think\response\Json
     */
    public function add()
    {
        $data = input();
        $db = new Finance();
        $last = $db->where(['pv_time' => strtotime('yesterday')])->find();
        $data['pv_time'] = strtotime($data['pv_time']);

        #判断键入日期的记录是否已经写入
        $count = Db::name('finance')->where(['pv_time' => $data['pv_time']])->count();
        if ($count) {
            return json(['status' => 'n', 'info' => date('Y-m-d', $data['pv_time']) . '已录入']);
        }

        #判断键入日期是否超过当前日期
        if ($data['pv_time'] > time()) {
            return json(['status' => 'n', 'info' => '请不要录入未结束的业绩']);
        }

        if (!$data['pv_time']) {
            return json(['status' => 'n', 'info' => '时间格式有误']);
        }

        $patten = '/^\d+$|^\d+\.\d{1,2}$/';
        if (!preg_match($patten, $data['pv'])) {
            return json(['status' => 'n', 'info' => '今日业绩有误']);
        }

        if (!empty($data['pv_a'])) {
            if (!preg_match($patten, $data['pv_a'])) {
                return json(['status' => 'n', 'info' => '总业绩有误']);
            }
        } else {
            //为空时拿到前一天的总业绩加上今天的业绩
            $data['pv_a'] = $last['pv'] + $data['pv'];
        }

        if (!preg_match($patten, $data['margin'])) {
            return json(['status' => 'n', 'info' => '利润格式有误']);
        }

        if (!empty($data['margin_a'])) {
            if (!preg_match($patten, $data['margin_a'])) {
                return json(['status' => 'n', 'info' => '总利润格式有误']);
            }
        } else {
            $data['margin_a'] = $last['margin_a'] + $data['margin'];
        }

        if (!is_numeric($data['member_num'])) {
            return json(['status' => 'n', 'info' => '会员增长数量有误']);
        }

        if (!empty($data['member_num_a'])) {
            if (!is_numeric($data['member_num_a'])) {
                return json(['status' => 'n', 'info' => '会员总数量有误']);
            }
        } else {
            $data['member_num_a'] = $last['member_num_a'] + $data['member_num'];
        }

        $data['add_time'] = time();
        $data['uid'] = session('user_id');

        $res = $db->allowField(true)->save($data);
        if ($res) {
            return json(['status' => 'y', 'info' => '添加成功']);
        } else {
            return json(['status' => 'n', 'info' => '操作失败']);
        }
    }

    /**
     * 编辑业绩
     * @return \think\response\Json
     */
    public function edit()
    {
        $data = input();
        $db = new Finance();
        $last = $db->where(['pv_time' => strtotime('yesterday')])->find();
        $data['pv_time'] = strtotime($data['pv_time']);

        #判断键入日期的记录是否已经写入
        $count = Db::name('finance')->where(['pv_time' => $data['pv_time']])->count();
        if ($count != 1) {
            return json(['status' => 'n', 'info' => date('Y-m-d', $data['pv_time']) . '已录入']);
        }

        #判断键入日期是否超过当前日期
        if ($data['pv_time'] > time()) {
            return json(['status' => 'n', 'info' => '请不要录入未结束的业绩']);
        }

        if (!$data['pv_time']) {
            return json(['status' => 'n', 'info' => '时间格式有误']);
        }

        $patten = '/^\d+$|^\d+\.\d{1,2}$/';
        if (!preg_match($patten, $data['pv'])) {
            return json(['status' => 'n', 'info' => '今日业绩有误']);
        }

        if (!empty($data['pv_a'])) {
            if (!preg_match($patten, $data['pv_a'])) {
                return json(['status' => 'n', 'info' => '总业绩有误']);
            }
        } else {
            //为空时拿到前一天的总业绩加上今天的业绩
            $data['pv_a'] = $last['pv'] + $data['pv'];
        }

        if (!preg_match($patten, $data['margin'])) {
            return json(['status' => 'n', 'info' => '利润格式有误']);
        }

        if (!empty($data['margin_a'])) {
            if (!preg_match($patten, $data['margin_a'])) {
                return json(['status' => 'n', 'info' => '总利润格式有误']);
            }
        } else {
            $data['margin_a'] = $last['margin_a'] + $data['margin'];
        }

        if (!is_numeric($data['member_num'])) {
            return json(['status' => 'n', 'info' => '会员增长数量有误']);
        }

        if (!empty($data['member_num_a'])) {
            if (!is_numeric($data['member_num_a'])) {
                return json(['status' => 'n', 'info' => '会员总数量有误']);
            }
        } else {
            $data['member_num_a'] = $last['member_num_a'] + $data['member_num'];
        }

        $data['add_time'] = time();
        $data['uid'] = session('user_id');

        $res = $db->allowField(true)->save($data, ['id' => $data['id']]);
        if ($res) {
            return json(['status' => 'y', 'info' => '添加成功']);
        } else {
            return json(['status' => 'n', 'info' => '操作失败']);
        }
    }

    /**
     * 删除记录
     * @return \think\response\Json
     */
    public function del()
    {
        if (Request::instance()->isAjax()) {
            $id = input('id');
            if (is_numeric($id)) {
                $res = Db::name('finance')->delete($id);
                if ($res) {
                    return json(['status' => 'y']);
                } else {
                    return json(['status' => 'n']);
                }
            } else {
                return json(['status' => 'n']);
            }
        }

    }

    /**
     * 我的k线
     */
    public function k_line()
    {
        return $this->fetch('finance/k_line');
    }

    /**
     * 我的股票
     * @return mixed
     */
    public function stock_info()
    {
        $info = Db::name('stockinfo')->where(['uid' => session('user_id')])->find();
        if (!empty($info)) {
            $this->assign('info', $info);
        }
        return $this->fetch('finance/stock_info');
    }

    /**
     * 查看股票信息
     * @return mixed
     */
    public function stock_detail()
    {
        $id = input('id');
        if (is_numeric($id)) {
            $info = Db::name('stockinfo')->where(['uid' => session('user_id')])->find();
            if ($info['status'] == 1) {
                return '<script type="text/javascript">alert("当前状态不可编辑");window.location.href=document.referrer;</script>';
            }
            $this->assign('info', $info);
        }
        return $this->fetch('finance/stock_detail');
    }

    /**
     * 添加我的股票
     * @return \think\response\Json
     */
    public function stock_add()
    {
        if (!Request::instance()->isAjax()) {
            return json(['status' => 'n']);
        }
        $data = input();
        if (!is_numeric($data['num_all'])) {
            return json(['status' => 'n', 'info' => '数量格式错误']);
        }
        $count = Db::name('stockinfo')->where(['uid' => session('user_id')])->count();
        if ($count >= 1) {
            return json(['status' => 'n', 'info' => '您已经拥有一支股票了']);
        }
        $patten = '/^\d+$|^\d+\.\d{1,2}$/';
        if (!preg_match($patten, $data['price'])) {
            return json(['status' => 'n', 'info' => '股价格式有误']);
        }
        $data['status'] = 0;
        $data['uid'] = session('user_id');
        $res = model('stockinfo')->allowField(true)->save($data);
        if ($res)
        {
            return json(['status' => 'y', 'info' => '添加成功']);
        }
        else
        {
            return json(['status' => 'n', 'info' => '操作失败']);
        }
    }

    /**
     * 修改我的股票
     * @return \think\response\Json
     */
    public function stock_edit()
    {
        if (!Request::instance()->isAjax()) {
            return json(['status'=>'n']);
        }

        $data=input();
        if(!is_numeric($data['num_all']))
        {
            return json(['status'=>'n','info'=>'数据格式有误']);
        }

        $db=model('stockinfo');
        $info = $db->where(['uid'=>session('user_id')])->find();
        if($info['num_sale']>$data['num_all']){
            return json(['status'=>'n','info'=>'开售数量不能小于售出数量']);
        }

        $patten = '/^\d+$|^\d+\.\d{1,2}$/';
        if (!preg_match($patten, $data['price'])) {
            return json(['status' => 'n', 'info' => '股价格式有误']);
        }

        $res=$db->allowField(['num_all','price','bank_name','bank_account'])->save($data,['id'=>$data['id']]);
        if($res!==false)
        {
            return json(['status'=>'y','info'=>'修改成功']);
        }
        else
        {
            return json(['status'=>'n','info'=>'操作失败']);
        }
    }
    public function get_sale_num()
    {
        if(!Request::instance()->isAjax())
        {
            return json(['status'=>'n','info'=>'']);
        }
        $id = session('user_id');
        if(!is_numeric($id))
        {
            return json(['status'=>'n','info'=>'']);
        }
        $info = Db::name('stockinfo')->where(['id'=>$id])->value('num_sale');
        if(!is_numeric($info))
        {
            return json(['status'=>'n','info'=>'查询结果为空']);
        }
        else
        {
            return json(['status'=>'y','num'=>$info]);
        }
    }
}