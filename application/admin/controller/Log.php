<?php

/**
 *  
 * @file   LogController.php  
 * @date   2016-10-9 18:23:24 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

class Log extends Common {

    public function index() {
        $where = array();
        $lists = db("admin_log")->where($where)->order('id desc')->paginate(10);
        $this->assign('lists', $lists);
		$this->assign('page',$lists->render());
        return $this->fetch();
    }

}
