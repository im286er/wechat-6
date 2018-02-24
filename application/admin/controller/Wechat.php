<?php


namespace app\admin\controller;

use app\common\model\Keyword;
use app\common\model\Material;
use mikkle\tp_wechat\Wechat as wechat_tools;

class Wechat extends Common
{
    /**
     * 关键字列表
     */
    function keyword_list($search = '', $size = 10)
    {
        $keyword = new Keyword();
        $where = [];
        if (!empty($search))
            $where['name'] = ['like', $search . "%"];
        $list = $keyword->where($where)->paginate(
            $size, false, ['query' => $this->request->param()]);
        $this->assign("list", $list);
        return $this->fetch("keywordList");
    }


    function keyword_info($id = 0)
    {
        if ($this->request->isAjax()) {
            $data = $this->request->only(['name', 'sort', 'status']);
            $keyword = new Keyword();
            $rule = [
                'name' => 'require|chsAlpha|unique:keyword',
                'sort' => 'require|number',
                'status' => 'require|number|between:0,1'
            ];
            $res = $this->validate($data, $rule);
            if ($res !== true) {
                return json(['status' => false, 'info' => $res]);
            }
            try {
                $res = $keyword->insertGetId($data);
            } catch (\Exception $e) {
                return json(['status' => false, 'info' => $e->getMessage()]);
            }
            return json(['status' => true, 'info' => "添加成功"]);
        } else {
            if ($id > 0) {
                $this->assign("info", Keyword::get($id)->toArray());
            }
            return $this->fetch("keywordInfo");
        }
    }

    function keyword_edit()
    {

    }

    function keyword_del($id = 0)
    {
        if (!is_numeric($id) || $id <= 0)
            return abort("400");

    }

    /**
     * 微信菜单
     */
    function menu_list()
    {
        $menu = wechat_tools::menu()->getMenu();
        $this->assign($menu);
        return $this->fetch("menuList");
    }

    function menu_edit($data = '')
    {
        if ($this->request->isPost()) {
            $res = wechat_tools::menu()->createMenu(json_decode($data, true));
            return json($res ? ['status' => true, 'info' => '提交成功'] : ['status' => true, 'info' => '提交失败']);

        }
        return $this->fetch("menuEdit");
    }

    function menu_delete()
    {
        $res = wechat_tools::menu()->deleteMenu();
        if ($res) {
            return "<script type='text/javascript'>alert('删除成功');window.history.go(-1);</script>";
        } else {
            return "<script type='text/javascript'>alert('删除失败');window.history.go(-1);</script>";
        }
    }

    /**
     * 素材列表
     * $type{0:图文;1:图片;2:音乐;3:视频}
     */
    function material_list($size = 10, $type = 0)
    {
        $material = new \app\common\model\Material();
        $list = $material->where(['type' => intval($type)])->order("id DESC")->paginate($size, false,
            ['query' => $this->request->param()]);
        $this->assign("list", $list);
        $this->assign("type",$type);
        $this->assign("typename", ['news', 'image', 'voice', 'video']);
        return $this->fetch("wechat/MaterialList");
    }

    /**
     * 素材同步
     */
    function material_syn()
    {
        $totalnum = wechat_tools::media()->getForeverCount();
        if (is_array($totalnum)) {
            set_time_limit(0);
            $Material = new Material();
            $Material::destroy(0);
            #图文消息
            $type = ['voice_count', 'video_count', 'image_count', 'news_count'];
            foreach ($type as $v) {
                $j = 20;

                if ($totalnum[$v] > 0) {
                    for ($i = 0; $i <= $totalnum[$v]; $i += $j) {
                        $tmpResource = wechat_tools::media()->getForeverList(explode('_', $v)[0], $i, $j);
                        halt($tmpResource);
                        #暂留 查看资源信息

                    }
                }
            }


        } else {
            return "<script type='text/javascript'>alert('同步失败');window.history.go(-1);</script>";
        }


    }
}

