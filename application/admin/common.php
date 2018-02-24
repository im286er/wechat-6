<?php
/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2017/2/20 0020
 * Time: 17:56
 */
function nodeTree($arr, $id = 0, $level = 0)
{

    static $array = array();

    foreach ($arr as $v) {

        if ($v['parentid'] == $id) {

            $v['level'] = $level;

            $array[] = $v;

            nodeTree($arr, $v['id'], $level + 1);

        }

    }

    return $array;

}
/**
 * 数组转树
 * @param type $list
 * @param type $root
 * @param type $pk
 * @param type $pid
 * @param type $child
 * @return type
 */

function list_to_tree($list, $root = 0, $pk = 'id', $pid = 'parentid', $child = '_child')
{

    // 创建Tree

    $tree = array();

    if (is_array($list)) {

        // 创建基于主键的数组引用

        $refer = array();

        foreach ($list as $key => $data) {

            $refer[$data[$pk]] = &$list[$key];

        }

        foreach ($list as $key => $data) {

            // 判断是否存在parent

            $parentId = 0;

            if (isset($data[$pid])) {

                $parentId = $data[$pid];

            }

            if ((string)$root == $parentId) {

                $tree[] = &$list[$key];

            } else {

                if (isset($refer[$parentId])) {

                    $parent = &$refer[$parentId];

                    $parent[$child][] = &$list[$key];

                }

            }

        }

    }

    return $tree;

}
function object_array($array)
{

    if (is_object($array)) {

        $array = (array)$array;

    }
    if (is_array($array)) {

        foreach ($array as $key => $value) {

            $array[$key] = object_array($value);

        }

    }

    return $array;

}
/**
 * 下拉选择框
 */

function select($array = array(), $id = 0, $str = '', $default_option = '')
{

    $string = '<select ' . $str . '>';

    $default_selected = (empty($id) && $default_option) ? 'selected' : '';

    if ($default_option)

        $string .= "<option value='' $default_selected>$default_option</option>";

    if (!is_array($array) || count($array) == 0)

        return false;

    $ids = array();

    if (isset($id))

        $ids = explode(',', $id);

    foreach ($array as $key => $value) {

        $selected = in_array($key, $ids) ? 'selected' : '';

        $string .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';

    }

    $string .= '</select>';

    return $string;

}
/**
 * 复选框
 *
 * @param $array 选项 二维数组
 * @param $id 默认选中值，多个用 '逗号'分割
 * @param $str 属性
 * @param $defaultvalue 是否增加默认值 默认值为 -99
 * @param $width 宽度
 */

function checkbox($array = array(), $id = '', $str = '', $defaultvalue = '', $width = 0, $field = '')
{

    $string = '';

    $id = trim($id);

    if ($id != '')

        $id = strpos($id, ',') ? explode(',', $id) : array($id);

    if ($defaultvalue)

        $string .= '<input type="hidden" ' . $str . ' value="-99">';

    $i = 1;

    foreach ($array as $key => $value) {

        $key = trim($key);

        $checked = ($id && in_array($key, $id)) ? 'checked' : '';

        if ($width)

            $string .= '<label class="ib" style="width:' . $width . 'px">';

        $string .= '<input type="checkbox" ' . $str . ' id="' . $field . '_' . $i . '" ' . $checked . ' value="' . $key . '"> ' . $value;

        if ($width)

            $string .= '</label>';

        $i++;

    }

    return $string;

}
/**
 * 单选框
 *
 * @param $array 选项 二维数组
 * @param $id 默认选中值
 * @param $str 属性
 */

function radio($array = array(), $id = 0, $str = '', $width = 0, $field = '')
{

    $string = '';

    foreach ($array as $key => $value) {

        $checked = trim($id) == trim($key) ? 'checked' : '';

        if ($width)

            $string .= '<label class="ib" style="width:' . $width . 'px">';

        $string .= '<input type="radio" ' . $str . ' id="' . $field . '_' . $key . '" ' . $checked . ' value="' . $key . '"> ' . $value;

        if ($width)

            $string .= '</label>';

    }

    return $string;

}
/**
 * test
 */
function info()
{
    echo '<prev>';
    print_r(phpinfo());
}
/**
 * 组装curl请求
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function curlbox_get($url)
{
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出 
	$result=curl_exec($ch); 
	curl_close($ch);
	return $result;
}
/**
 * 三方登录
 * @param  [type] $username [description]
 * @param  [type] $password [description]
 * @return [type]           [description]
 */
function getWarningFromJjs()
{
	$param = config('crond.param');
	$url = config('crond.url');

	$data['param1'] = $param[0];
	$data['param2'] = $param[1];
	$data['key'] = md5(md5($data['param1'].date('Ymd').$data['param2']));

	$res = json_decode(ch($url,$data),true);
	return $res;
}
/**
 * curlpost
 * @param  [type] $url       [description]
 * @param  [type] $post_data [description]
 * @return [type]            [description]
 */
function ch($url,$post_data)
{
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    //设置URL，可以放入curl_init参数中
    curl_setopt($ch, CURLOPT_USERAGENT,'');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    //设置UA
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。 如果不加，即使没有echo,也会自动输出
    $content = curl_exec($ch);
    //执行
    curl_close($ch);
    //关闭
   return $content;
}



/**
 * 递归组合分类
 * @param array $data   分类信息
 * @param int $parent_id   上级ID
 * @param int $level  分类等级
 * @return array
 */
function category__merge($data,$parent_id=0,$level=0){
    $res = array();
    foreach ($data as $v){
        if($v['parent_id'] == $parent_id){
            $v['level'] = $level;
            $v['disabled'] = false;
            foreach($data as $v1){
                if($v1['parent_id'] == $v['cat_id']){
                    $v['disabled'] = true;
                    break;
                }
            }
            $res[] = $v;
            //找子分类
            $res = array_merge($res,category__merge($data,$v['cat_id'],$level+1));
            //array_merge()是重新组合数组的函数/数组的组合
        }
    }
    return $res;
}

/**
 * 递归获取分类下的所有子分类
 * @param array $data   分类信息
 * @param int $cat_id     分类ID
 * @return array
 */
function get_children_id($data,$cat_id){
    $res = array();
    foreach($data as $v){
        if($v['parent_id'] == $cat_id){
            $res[] = $v['cat_id'];
            $res = array_merge($res,get_children_id($data,$v['cat_id']));
        }
    }
    return $res;
}

/**
 * 根据id获取分类名称
 * @param int $id
 */
function get_category_name($id,$table='news_category'){
    return db($table)->where(array('cat_id'=>$id))->value('cat_name');
}
/**
 * @param string $list 数据
 * @param string $key 主键
 * @param string $pKey 父键
 * @param int $pid 要获取的Pid
 * @param int $level 等级
 * @return array
 */
function parentList($list = [], $key, $pKey, $pid = 0, $level = 0)
{
    $tmp = array ();
    foreach ($list as $v) {
        if ($v[$pKey] == $pid) {
            $v['level'] = $level;
            $tmp[] = $v;
            $tmp = array_merge($tmp, parentList($list, $key, $pKey, $v[$key], $level + 1));
        }
    }
    return $tmp;
}
