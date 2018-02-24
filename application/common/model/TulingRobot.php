<?php
/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2018/2/23 
 * Time: 17:33
 */

namespace app\common\model;

use app\Common\model\CurlT;
use think\config;
use think\Hook;
class TulingRobot
{
	public $options = array();
	private $appKey = '';
	private $url = '';
	public $reply='';
	public $userid = '';
	
	public function __construct($options=array())
	{
		$this->options = !empty($options) ? $options : $this->options;
        if (empty($options)&& !empty( Config::get("tuling.default_options_name"))){
            $this->options = Config::get("tuling")[Config::get("tuling.default_options_name")];
        }elseif(is_string($options)&&!empty( Config::get("tuling.$options"))){
            $this->options = Config::get("tuling.$options");
        }
        if(empty($this->options)){
            throw new Exception("图灵机器人配置参数错误");
        }
        $this->appKey = $this->options['appKey'];
        $this->url = $this->options['url'];
	}

	public function getReply($user_id=1,$content='')
	{
		$data = array(
			'key'=>$this->appKey,
			'info'=>$content,
			'userid'=>$user_id
		);
		$curl = new CurlT($this->url,$data);
		$reply = $curl->curl_post('json');
		Hook::listen("tuling_reply", array('from'=>$user_id,));
		if($reply === false)
		{
			return 'sorry!connection aborted.';
		}else{
			$reply=json_decode($reply,true);
			return $reply['text'];
		}
	}
}