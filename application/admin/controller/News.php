<?php
/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2017/2/15
 * Time: 14:25
 */

namespace app\admin\controller;

use app\admin\model\NewsCategory;
use think\Controller;
use think\Db;
use think\Request;

class News extends Controller
{
    /**
     * 新闻分类列表
     */
    public function category()
    {
        $list = db('news_category')->order('sort')->select();
        $tree = parentList($list, 'id', 'pid', 0);

        foreach ($tree as &$v) {
            $v['pName'] = Db::name('news_category')->where('id', $v['pid'])->value('cat_name');
        }
        $this->assign('list', $tree);
        return $this->fetch('news/category');
    }

    /**
     * 新闻分类详细页
     */
    public function categoryInfo()
    {
        $id = input('id');
        $child = array();
        $list = db('news_category')->order('sort')->select();
        $tree = parentList($list, 'id', 'pid', 0);
        if (is_numeric($id)) {
            $info = db('news_category')->find($id);
            $this->assign('info', $info);
            $tmpChild = parentList($list, 'id', 'pid', $id);
            $child[] = $id;
            foreach ($tmpChild as $v) {
                $child[] = $v['id'];
            }
        }
        $this->assign('child', $child);
        $this->assign('list', $tree);
        return $this->fetch('news/categoryInfo');
    }

    /*
     * 分类添加
     */
    public function categoryAdd()
    {
        $data = input();
        $count = db('news_category')->where(['cat_name' => $data['cat_name']])->count();
        if ($count) {
            return json(['status' => 'n', 'info' => '分类已存在']);
        }
        $count = db('news_category')->where(['id' => $data['pid']])->count();
        if ($count == 0 && $data['pid'] != 0) {
            return json(['status' => 'n', 'info' => '父级不存在']);
        }
        if (!is_numeric($data['sort'])) {
            return json(['status' => 'n', 'info' => '序号有误']);
        }

        $res = model('news_category')->allowField(true)->save($data);
        if ($res) {
            return json(['status' => 'y', 'info' => '添加成功']);
        } else {
            return json(['status' => 'n', 'info' => '操作失败']);
        }
    }

    /**
     * 分类修改
     */
    public function categoryEdit()
    {
        $data = input();
        if (!is_numeric($data['sort'])) {
            return json(['status' => 'n', 'info' => '序号有误']);
        }
        $count = db('news_category')->where(['id' => $data['pid']])->count();
        if ($count == 0) {
            return json(['status' => 'n', 'info' => '父级不存在']);
        }
        $res = model('news_category')->allowField(true)->save($data, ['id' => $data['id']]);
        if ($res !== false) {
            return json(['status' => 'y', 'info' => '操作成功']);
        } else {
            return json(['status' => 'n', 'info' => '操作失败']);
        }
    }

    /**
     * 分类删除
     */
    public function categoryDel()
    {
        $id = input('id');
        if (!is_numeric($id)) {
            return json(['status' => 'n']);
        }
        $list = Db::name('news_category')->select();
        $cList = parentList($list, 'id', 'pid', $id);
        $list[] = $id;
        foreach ($cList as $v) {
            $list[] = $v['id'];
        }
        $res = NewsCategory::destroy(implode(',', $list));
        if ($res == 1) {
            return json(['status' => 'y']);
        } else if ($res > 1) {
            return json(['status' => 'yes']);
        } else {
            return json(['status' => 'n']);
        }
    }

    /**
     * 文章列表
     */
    public function newsList()
    {
        $size = 10;
        $where = '';
        $content = input('searchContent');
        if ($content != '' && $content != null) {
            $where = "title like '" . $content . "%' or ";
            $where .= "cat_name like   '" . $content . "%' ";
            $this->assign('key', $content);
        }
        $db = Db::name('news');
        $list = $db->field('a.id,a.title,a.sort,a.hot,a.status,a.savetime,a.click,b.cat_name')
            ->alias('a')->join(config('database.prefix') . 'news_category b', 'a.cat_id=b.id', 'LEFT')->where($where)
            ->order('a.id desc')->paginate($size, false, ['query' => Request::instance()->param()]);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch('/news/newsList');
    }

    /**
     * 文章详情
     */
    public function newsInfo()
    {
        $id = input('id');
        if (is_numeric($id)) {
            $info = Db::name("news")->find($id);
            $info['content'] = htmlspecialchars_decode($info['content']);
            $this->assign('info', $info);
        }
        $category = Db::name('news_category')->select();
        $tree = parentList($category, 'id', 'pid', 0, 0);
        $this->assign('list', $tree);
        return $this->fetch('/news/newsInfo');
    }

    /**
     * 文章添加
     */
    public function newsAdd()
    {
        $data = input();
        if (empty($data['title'])) {
            return json(['status' => 'n', 'info' => '标题缺失']);
        }
        // if(empty($data['thumb']))
        // {
        //     return json(['status'=>'n','info'=>'图片缺失']);
        // }
        if (empty($data['content'])) {
            return json(['status' => 'n', 'info' => '文章缺失']);
        }
        if (!is_numeric($data['sort'])) {
            return json(['status' => 'n', 'info' => '序号缺失']);
        }
        if (!in_array($data['hot'], [0, 1])) {
            return json(['status' => 'n', 'info' => '推荐错误']);
        }
        if (!in_array($data['status'], [0, 1])) {
            return json(['status' => 'n', 'info' => '推荐错误']);
        }
        if (!is_numeric($data['click'])) {
            return json(['status' => 'n', 'info' => '点击量错误']);
        }
        $cate = db('news_category')->where(['id' => $data['cat_id']])->find();
        if (empty($cate)) {
            return json(['status' => 'n', 'info' => '父级不存在']);
        }
        $data['cat_name'] = $cate['cat_name'];
        if (empty($data['introduction'])) {
            $data['introduction'] = msubstr(strip_tags($data['content']), 200, 0, 'utf-8', false);
        }
        $data['addtime'] = time();
        $data['content'] = htmlspecialchars($_POST['content']);
        $res = model('news')->allowField(true)->save($data);
        if ($res) {
            return json(['status' => 'y', 'info' => '添加成功']);
        } else {
            return json(['status' => 'n', 'info' => '操作失败']);
        }
    }

    /**
     * 文章编辑
     */
    public function newsEdit()
    {
        $data = input();
        if (empty($data['title'])) {
            return json(['status' => 'n', 'info' => '标题缺失']);
        }
        // if(empty($data['thumb']))
        // {
        //     return json(['status'=>'n','info'=>'图片缺失']);
        // }
        if (empty($data['content'])) {
            return json(['status' => 'n', 'info' => '文章缺失']);
        }
        if (!is_numeric($data['sort'])) {
            return json(['status' => 'n', 'info' => '序号缺失']);
        }
        if (!in_array($data['hot'], [0, 1])) {
            return json(['status' => 'n', 'info' => '推荐错误']);
        }
        if (!in_array($data['status'], [0, 1])) {
            return json(['status' => 'n', 'info' => '推荐错误']);
        }
        if (!is_numeric($data['click'])) {
            return json(['status' => 'n', 'info' => '点击量错误']);
        }
        $cate = db('news_category')->where(['id' => $data['cat_id']])->find();
        if (empty($cate)) {
            return json(['status' => 'n', 'info' => '父级不存在']);
        }
        $data['cat_name'] = $cate['cat_name'];
        if (empty($data['introduction'])) {
            $data['introduction'] = msubstr(strip_tags($data['content']), 200);
        }
        if (!empty($data['oldPic']) && $data['oldPic'] != $data['thumb']) {
            @unlink($data['oldPic']);
        }
        $data['savetime'] = time();
        $data['content'] = htmlspecialchars($_POST['content']);
        $res = model('news')->allowField(true)->save($data, ['id' => $data['id']]);
        if ($res) {
            $result = ['status' => 'y', 'info' => '操作成功'];
        } else {
            $result = ['status' => 'n', 'info' => '操作失败'];
        }
        return json($result);
    }

    /**
     * 删除
     */
    public function newsDel()
    {
        $id = input('id');
        $res = db('news')->where(['id' => $id])->delete();
        if ($res) {
            $data = array('status' => 'y');
        } else {
            $data = array('status' => 'n');
        }
        return json($data);
    }
}