<?php

/**
 *  系统设置
 * @file   ConfigController.php
 * @date   2017年5月31日
 * @author xianghua wen
 * @version
 */

namespace app\admin\controller;

class Config extends Common
{

    public function index()
    {
        return $this->fetch();
    }

    /**
     * @description 配置设置
     * @return string|\think\response\Json
     */
    public function lists()
    {
        if (request()->isAjax()) {
            $post = input('post.');
            foreach ($post as $k => $v) {
                $row = db("config")->where(array('name' => $k))->find();
                if (!empty($row)) {  //修改
                    db("config")->where(array('id' => $row['id']))->update(['value'=>$v]);
                } else {  //添加
                    $data = array(
                        'name' => $k,
                        'value' => $v
                    );
                    db("config")->insertGetId($data);
                }
            }
            return json(['status'=>'y','info'=>'操作成功']);
        }
        $list = db("config")->select();
        $data = array();
        foreach ($list as $v) {
            $data[$v['name']] = $v['value'];
        }
        $this->assign("list",$list);
        $this->assign('data', $data);
        return $this->fetch();
    }
}
