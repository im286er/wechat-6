<?php
/**
 * Created by PhpStorm.
 * User: Developer
 * Date: 2018/2/24
 * Time: 11:53
 */

namespace app\index\behavior;

use app\common\model\Visitor;
use app\common\model\VisitLog;

class Receive
{
    public function run(\mikkle\tp_wechat\src\Receive $receive)
    {
        #判断访客是否存在于表中
        $visitor = new Visitor();
        $visitor_id = $visitor->where(array('from' => $receive->getRevFrom()))->value("id");
        $where = '';
        if (!$visitor_id) {
            $visitor_data = array(
                'from' => $receive->getRevFrom(),
                'first_visit_time' => date("Y-m-d H:i:s", $receive->getRevCtime())
            );
        } else {
            $visitor_data = array(
                'id' => $visitor_id,
                'last_visit_time' => date("Y-m-d H:i:s", $receive->getRevCtime())
            );
            $where = array('id'=>$visitor_id);
        }
        $visitor->save($visitor_data,$where);
        $visitor_id = $visitor_id?$visitor_id:$visitor->getLastInsID();
        #插入访问记录
        $visit_history = new VisitLog();
        $visit_history->save(array(
            'visitor_id'       => $visitor_id,
            'message_content'  => $receive->getRevContent(),
            'message_type'     => $receive->getRevType(),
            'visit_time'       => date("Y-m-d H:i:s", $receive->getRevCtime())
        ));
        $receive->receive_id = $visit_history->getLastInsID();
        $receive->visitor_id = $visitor_id;
    }
}