<?php
/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2018/2/23 
 * Time: 17:33
 */

namespace app\common\model;

use think\config;
use think\Hook;
use think\Exception;
class TulingRobot
{
	public $options = array();
	private $appKey = '';
	private $url = '';
	public $reply='';
	public $userid = '';

    /**
     * TulingRobot constructor.
     * @param array $options
     * @throws Exception
     */
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
		if($reply!==false){ 
		    $reply=json_decode($reply,true)['text'];
		}else{
		    $reply='sorry!connection aborted.';
		}
		return $reply;
	}
}
