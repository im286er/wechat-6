<?php
/**
 * Created by PhpStorm.
 * User: xianghua
 * Date: 2017/6/8 0008
 * Time: 15:29
 */

namespace app\admin\controller;

use \think\Db;

class Product extends Common
{
    #资讯分类列表
    public function category()
    {
        $count = db("product_category")->count();
        $list = db("product_category")->order('sort')->select();
        $list = category__merge($list);
        $this->assign('list', $list);
        $this->assign('count', $count);
        return $this->fetch();
    }

    public function category_add()
    {
        $db = db("product_category");
        if (request()->isAjax()) {
            $data = input("post.");
            if ($_POST['cat_id'] > 0) {
                $bool = $db->update($data);
            } else {
                $bool = $db->insertGetId($data);
            }

            if ($bool !== false) {
// 				echo json_encode(array('status'=>'y','info'=>'添加成功'));
                return json(array ( 'status' => 'y', 'info' => '保存成功' ));  //ajax返回方式
            } else {
// 				echo json_encode(array('status'=>'n','info'=>'添加失败'));
                return json(array ( 'status' => 'n', 'info' => '保存失败' ));
            }
        }
        $list = $db->order('sort')->select();
        $list = category__merge($list);
        $this->assign('list', $list);
        $id = input('id');
        $children = [];
        if ($id > 0) {
            $list = $db->field('cat_id,parent_id')->select();
            $child = category__merge($list, $id);
            $children[] = $id;
            foreach ($child as $value) {
                $children[] = $value['id'];
            }
            $row = $db->find($id);

            $this->assign('row', $row);
        }
        $this->assign('children', $children);
        return $this->fetch();
    }

    //删除功能
    public function category_del()
    {
        $id = input('id');
        $db = db('product_category');
        $list = $db->select();
        $id = explode(',', trim($id, ','));
        $where['cat_id'] = array ( 'in', $id );
        $b = $db->where($where)->delete();
        if ($b) {
            //删除这些分类的所有子分类
            $children = array ();
            foreach ($id as $v) {
                $children = array_merge($children, get_children_id($list, $v));
            }
            if (!empty($children)) {
                $where['cat_id'] = array ( 'in', $children );
                $b1 = $db->where($where)->delete();
                if ($b1) {
                    $count = $db->count();
                    return json(array ( 'status' => 'y', 'info' => $children, 'count' => $count ));
                }
            }
            $count = $db->count();
            return json(array ( 'status' => 'y', 'info' => '', 'count' => $count ));
        } else {
            return json(array ( 'status' => 'n', 'info' => '删除失败' ));
        }
    }

    //商品列表
    public function product()
    {
        $size = 10;
        $db = db('product');
        $cat_id = input('cat_id');
        $where['is_delete'] = 0;  //等价于$where['is_delete'] = array('eq',0);
        $category = db('product_category')->order('sort')->select();

        if (is_numeric($cat_id)) {
            $children = get_children_id($category, $cat_id);
            $children[] = $cat_id;
            $where['cat_id'] = array ( 'in', $children );
            $this->assign('cat_id', $cat_id);
        }

        $start_time = input('start_time');
        $end_time = input('end_time');
        if (!empty($start_time)) {  //开始时间不为空
            if (!empty($end_time)) {  //结束时间不为空
                $start_time = strtotime($start_time);
                $end_time = strtotime($end_time);
                $where['publish_time'] = array ( 'between', array ( $start_time, $end_time ) );
            } else {     //结束时间为空
                $start_time = strtotime($start_time);
                $where['publish_time'] = array ( 'egt', $start_time );
            }
        } else {    //开始时间为空
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $where['publish_time'] = array ( 'elt', $end_time );
            }
        }

        $title = input('title');
        if (!empty($title)) {
            $where['goods_name'] = array ( 'like', "%$title%" );
        }
        $count = $db->where($where)->count();
        $list = $db->where($where)->paginate($size, false, [ 'query' => request()->instance()->param() ]);
        $this->assign('list', $list);   // 赋值数据集
        $this->assign('page', $list->render());   // 赋值分页输出
        $this->assign('count', $count);
        $category = category__merge($category);
        $this->assign('category', $category);

        return $this->fetch();
    }


    #添加资讯
    public function product_add()
    {
        $db = db('product');
        if (request()->isAjax()) {
            $data = input("post.");
            if ($_POST['id'] > 0) {
                unset($data['oldPic']);
                $b = $db->update($data);
            } else {
                unset($data['oldPic']);
                $b = $db->insertGetId($data);
            }
            if ($b !== false) {
                if (!empty($_POST['oldPic']) && $_POST['oldPic'] != $_POST['thumb']) {
                    @unlink('.' . __ROOT__ . $_POST['oldPic']);
                }
                return json(array ( 'status' => 'y', 'info' => '保存成功' ));  //ajax返回方式
            } else {
                return json(array ( 'status' => 'n', 'info' => '保存失败' ));
            }
        } else {
            $list = db('product_category')->order('sort')->select();
            $list = category__merge($list);
            $this->assign('list', $list);

            $id = input('id');
            if ($id > 0) {
                $row = $db->find($id);
                $row['publish_time'] = date('Y-m-d H:i:s', $row['publish_time']);
            } else {
                $row = array ( 'publish_time' => date('Y-m-d H:i:s'), 'click_count' => mt_rand(0, 10000), 'is_sale' => 1, 'tmp_number' => 5 );
            }
            $this->assign('row', $row);
            return $this->fetch();
        }
    }

    /**
     * 商品删除
     * @return \think\response\Json
     */
    public function product_del()
    {
        $id = input('id');
        $id = trim($id, ',');
        $db = db("product");
        $where = "id in ($id)";
        $imgs = $db->where($where)->column('thumb');
        //$contents = $db->where($where)->getField('content',true);
        $b = $db->where($where)->delete();
        if ($b) {
            foreach ($imgs as $v) {
                @unlink('.' . $v);
            }
            $count = $db->count();
            return json(array ( 'status' => 'y', 'count' => $count ));
        } else {
            return json(array ( 'status' => 'n' ));
        }
    }

    /**
     * 兑换记录
     */
    public function orderlist()
    {
        $size = 10;

        $username = input("username");
        if (!empty($username)) {
            $id = \db("member")->where([ 'account' => $username ])->value("id");
            $where['userid'] = [ 'eq', $id ];
        }
        $list = db("order")->where($where)->order("status")->paginate($size, false, [ 'query' => request()->instance()->param() ]);
        $this->assign("list", $list);
        return $this->fetch();
    }

    /**
     * 确认发货
     */
    public function ensure_send()
    {
        if (request()->isAjax()) {
            $id = intval(input("id"));
            $res = Db::name("order")->where([ 'id' => $id ])->update([
                'date_c' => time(),
                'status' => 1
            ]);
            if ($res) {
                return json([ 'status' => 'y', 'info' => '发货成功' ]);
            } else {
                return json([ 'status' => 'n', 'info' => '操作失败' ]);
            }
        }
    }
}