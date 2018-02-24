<?php

/**
 *  
 * @file   GroupController.php  
 * @date   2016-9-1 15:11:41 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

class Group extends Common {

    public function index() {
        $res = db('admin_group')->select();
        $this->assign('list', $res);
        return $this->fetch();
    }

    /*
     * 查看
     */

    public function info() {
        $id = input('id');
        if ($id)
        {
            //当前用户信息
            $info = db('admin_group')->find($id);
            $this->assign('info', $info);
        }

        return $this->fetch();
    }

    /*
     * 添加
     */

    public function add() {
        $data = input();
        if($data['name']==''||!isset($data['name']))
        {
            return json(['status'=>'n','info'=>'数据缺失']);
        }
        if($data['description']==''||!isset($data['description']))
        {
            return json(['status'=>'n','info'=>'数据缺失']);
        }
        if($data['listorder']==''||!isset($data['listorder']))
        {
            return json(['status'=>'n','info'=>'数据缺失']);
        }
        $res = model('Admin_group')->allowField(true)->save($data);
        if ($res)
        {
            return json(['status'=>'y','info'=>'操作成功']);
        }
        else
        {
            return json(['status'=>'n','info'=>'操作失败']);
        }
    }

    /*
     * 修改
     */

    public function edit() {
        $data = input();
        if($data['name']==''||!isset($data['name']))
        {
            return json(['status'=>'n','info'=>'数据缺失']);
        }
        if($data['description']==''||!isset($data['description']))
        {
            return json(['status'=>'n','info'=>'数据缺失']);
        }
        if($data['listorder']==''||!isset($data['listorder']))
        {
            return json(['status'=>'n','info'=>'数据缺失']);
        }
        $data['updatetime'] = time();
        $res = model('Admin_group')->allowField(true)->save($data, ['id' => $data['id']]);
        if ($res)
        {
            return json(['status'=>'y','info'=>'操作成功']);
        }
        else
        {
            return json(['status'=>'n','info'=>'操作失败']);
        }
    }

    /*
     * 删除
     */

    public function del() {
        $id = input('id');
        $res = db('admin_group')->where(['id' => $id])->delete();
        if ($res)
        {
            db('admin_group_access')->where(['group_id'=>$id])->delete();
            return json(['status'=>'y']);
        }
        else
        {
            return json(['status'=>'n']);
        }
    }

    /**
     * 权限
     */
    public function rule() {

        //echo APP_PATH;exit;
        if (input('gid')) {
            $gid = input('gid');
			$where['id']=['eq',$gid];
			$where1['is_delete']=['neq','1'];
            $rules = db('admin_group')->where($where)->value('rules');
            $this->assign('rules',$rules);
            
            $menu = db('menu')->where($where1)->order('listorder asc')->select();
            $this->assign('menu', list_to_tree($menu));
            return $this->fetch();
        }
    }

    public function editRule() {
        $post = input();
        if ($post['id']) {
            $where = ['id' => $post['id']];

            $rules = implode(',', $post['rules']);
            $data = array();
            $data['updatetime'] = time();
            $data['rules'] = $rules;

            $res = model('admin_group')->save($data, $where);

            if ($res) {
                return json(['status'=>'y']);
            } else {
                return json(['status'=>'n','操作失败']);
            }
        }
    }

}
