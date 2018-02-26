<?php

namespace app\index\controller;

use app\common\model\Music;
use mikkle\tp_wechat\WechatApi;
use app\common\model\TulingRobot;
use think\Hook;
use think\Exception;
use app\common\music\Kugou;
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
            $this->weObj->reply_tuling_content  = $res;
            $this->weObj->reply_tuling_type     = $type;
            Hook::listen("tuling_reply",$this->weObj);
        } else {
            if ($param[0] == '歌曲') {
                $search = $param[1];
                $song = new Kugou($search);
                $search_list = $song->search(1,1);
                if(isset($search_list['status'])) {
                    return $search_list['info'];
                }
                $music = $song->getMusic($search_list[0]['hash'],$search_list[0]['album_audio_id']);
                if(count($music)==0) {
                    return 'ops!something goes down.';
                }
                $res=[];
                $res['type']                    ='music';
                $res['message']['title']        = $music['song_name'];
                $res['message']['desc']         = $music['audio_name'];
                $res['message']['musicurl']     = $music['play_url'];
                $res['message']['hgmusicurl'] = $music['play_url'];
                $res['message']['thumbmediaid'] = '';
            }
        }
        return $res;
    }

    function test()
    {

    }
}
