<?php

/**
 *  
 * @file   Menu.php  
 * @date   2016-8-30 11:46:22 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

use think\Loader;
use think\Request;
class Menu extends Common {

    public function index() {
        $res = db('menu')->where('is_delete <> 1')->order('listorder asc')->select();
        $lists = nodeTree($res);
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /*
     * 查看
     */

    public function info() {
        $id = input('id');
        if ($id) {
            //当前用户信息
            $info = db('menu')->find($id);
            $this->assign('info', $info);
        }

        //下拉菜单
        $menu=Loader::model('Menu')->selectMenu();
        $this->assign('selectMenu', $menu);
        return $this->fetch();
    }

    /*
     * 添加
     */

    public function add() {
		if(Request::instance()->isAjax()){
			$data = input('post.');
			if ($data['parentid'] == null) {
				$data['parentid'] = 0;
			}
			if(empty($data['icon']))
			{
				return json(['status'=>'n','info'=>'菜单图标不能为空']);
			}
			if(empty($data['name']))
			{
				return json(['status'=>'n','info'=>'菜单名不能为空']);
			}
			if(empty($data['c']))
			{
				return json(['status'=>'n','info'=>'文件名不能为空']);
			}
			if(empty($data['a'])&&!empty($data['parentid']))
			{
				return json(['status'=>'n','info'=>'方法名不能为空']);
			}
			if(empty($data['display'])or !is_numeric($data['display']))
			{
				return json(['status'=>'n','info'=>'显示参数有误']);
			}
			$res = model('menu')->allowField(true)->save($data);
			if ($res) {
				return json(['status'=>'y','info'=>'操作成功']);
			} else {
				return json(['status'=>'n','info'=>'操作失败']);
			}
		}
        
    }

    /*
     * 修改
     */

    public function edit() {
		if(Request::instance()->isAjax())
		{
			$data = input();
			if(!is_numeric($data['id'])){return ;}
			$data['updatetime'] = time();
			if ($data['parentid'] == null) {
				$data['parentid'] = 0;
			}
			if(empty($data['icon']))
			{
				return json(['status'=>'n','info'=>'菜单图标不能为空']);
			}
			if(empty($data['name']))
			{
				return json(['status'=>'n','info'=>'菜单名不能为空']);
			}
			if(empty($data['c']))
			{
				return json(['status'=>'n','info'=>'文件名不能为空']);
			}
			if(empty($data['a'])&&!empty($data['parentid']))
			{
				return json(['status'=>'n','info'=>'方法名不能为空']);
			}
			if(empty($data['display'])or !is_numeric($data['display']))
			{
				return json(['status'=>'n','info'=>'显示参数有误']);
			}
			$res = model('menu')->allowField(true)->save($data, ['id' => $data['id']]);
			if ($res) {
				return json(['status'=>'y','info'=>'操作成功']);
			} else {
				return json(['status'=>'n','info'=>'操作失败']);
			}
		}
        
    }

    /*
     * 删除
     */

    public function del() {
		if(Request::instance()->isAjax())
		{
			$id = input('id');
			if(!is_numeric($id))
			{
				return json(['status'=>'n']);
			}
			$res = db('menu')->where(['id' => $id])->delete();
			if ($res) {
				return json(['status'=>'y']);
			} else {
				return json(['status'=>'n']);
			}
		}
        
    }

    /**
     * 排序
     */
    public function setListorder() {

        if ($_POST['listorder']) {
            $listorder = $_POST['listorder'];
            foreach ($listorder as $k => $v) {
                $data = array();
                $data['listorder'] = $v;
                $data['updatetime'] = time();
                $res = db('menu')->where(['id' => $k])->update($data);
            }
            if ($res) {
                $this->success('操作成功', url('index'));
            } else {
                $this->error('操作失败');
            }
        }
    }

}
