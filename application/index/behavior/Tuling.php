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

class Tuling
{
    protected static $tuling_visitor_id = 0;

    /**
     * 图灵机器人接口获得消息后HOOK
     * @param \mikkle\tp_wechat\src\Receive $receive
     */
    public function reply(\mikkle\tp_wechat\src\Receive $receive)
    {
        $visitor = new Visitor();
        $visitor_data = array(
            'id' => self::$tuling_visitor_id,
            'last_visit_time' => date("Y-m-d H:i:s", $receive->getRevCtime())
        );
        $where = array('id' => self::$tuling_visitor_id);
        $visitor->save($visitor_data, $where);
        #插入访问记录
        $visit_history = new VisitLog();
        $visit_history->save(array(
            'visitor_id' => self::$tuling_visitor_id,
            'message_content' => $receive->reply_tuling_content,
            'message_type' => $receive->reply_tuling_type,
            'visit_time' => date("Y-m-d H:i:s")
        ));
    }
}