<?php

namespace app\index\controller;

use app\common\model\Music;
use mikkle\tp_wechat\WechatApi;
use app\common\model\TulingRobot;
use think\Hook;
use think\Exception;

class Index extends WechatApi
{
    /**
     * 微信接收主方法
     * Power: Mikkle
     * Email：776329498@qq.com
     */
    public function index()
    {
        parent::index();
    }
    function returnMessageText()
    {
        $text = $this->weObj->getRevContent();
        $type = $this->weObj->getRevType();
        $param = explode("#", $text);
        if (count($param) < 2) {
            $from = $this->weObj->getRevFrom();
            try{
                $robot = new TulingRobot();
                $res = $robot->getReply($from,$text);
            }catch (\Exception $e){
                $res = $e->getMessage();
            }
            $this->weObj->reply_tuling      = $res;
            $this->weObj->reply_tuling_type = $type;
            Hook::listen("tuling_reply",$this->weObj);
        } else {
            if ($param[0] == '歌曲') {
                $search = $param[1];
                $song = new Music($search);
                if (!empty($song->error))
                    return $song->error;
                $res = [
                    'type'    => 'music',
                    'message' => [
                        'title'         => $song->fsong,
                        'desc'          => $song->fsinger . $song->fsinger2,
                        'musicurl'      => $song->musicUrl,
                        'hgmusicurl'    => $song->musicUrl,
                        'thumbmediaid'  => ''
                    ]
                ];
            }
        }
        return $res;
    }

    function test()
    {
        halt($this->options);
        $song = new Music("周杰伦");
        print_r($song);
    }
}
