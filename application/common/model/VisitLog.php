<?php
/**
 * Created by PhpStorm.
 * User: Developer
 * Date: 2018/2/24
 * Time: 13:36
 */

namespace app\common\model;

use think\model;

class VisitLog extends model
{
    protected $visitor_id;
    protected $message_type;
    protected $message_content;
    protected $visit_time;
    protected $name = 'visit_history';
}