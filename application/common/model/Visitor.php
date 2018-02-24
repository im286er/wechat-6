<?php
/**
 * Created by PhpStorm.
 * User: xxp
 * Date: 2017/11/16
 * Time: 10:50
 */

namespace app\common\model;


use think\Model;

class Visitor extends Model
{
	protected $id;
	protected $from;
	protected $first_visit_time;
	protected $name="visitor";
}