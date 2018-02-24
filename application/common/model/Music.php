<?php
/**
 * Created by PhpStorm.
 * User: xxp
 * Date: 2017/11/17
 * Time: 10:17
 */

namespace app\common\model;

class Music
{
    const music_search = "http://s.music.qq.com/fcgi-bin/music_search_new_platform?n=1&format=json";
    const music_stream_url = "http://ws.stream.qqmusic.qq.com/";
    const music_stream_url_sub = ".m4a?fromtag=46";
    const MUSIC_COVER_IMG_URL_PREV = "http://imgcache.qq.com/music/photo/mid_album_90/";
    public $id;
    public $chinesesinger;
    public $f;
    public $fsinger;
    public $fsinger2;
    public $fsong;
    public $pubTime;
    public $singerMID;
    public $singerMID2;
    public $singerid;
    public $singerid2;
    public $error;
    protected $sourcedata;
    public $coverImgUrl;
    public $musicUrl;

    public function __construct($sc = '')
    {
        $curl = new CurlT(self::music_search, $data = ['w' => $sc]);
        $data = $curl->curl_get();
        if ($data === false) {
            $this->error = $curl->error;
        } else {
            $this->sourcedata = json_decode($data, true)['data']['song']['list'];
            if (!empty($this->sourcedata)) {
                $this->sourcedata = array_pop($this->sourcedata);
                $tmpMusicData = explode("|", $this->sourcedata['f']);
                $this->f = $this->sourcedata['f'];
                $this->id = $tmpMusicData[0];
                $this->chinesesinger = $this->sourcedata['chinesesinger'];
                $this->fsinger = $this->sourcedata['fsinger'];
                $this->fsinger2 = $this->sourcedata['fsinger2'];
                $this->fsong = $this->sourcedata['fsong'];
                $this->pubTime = $this->sourcedata['pubTime'];
                $this->singerMID = $this->sourcedata['singerMID'];
                $this->singerMID2 = $this->sourcedata['singerMID2'];
                $this->singerid = $this->sourcedata['singerid'];
                $this->singerid2 = $this->sourcedata['singerid2'];
                $this->musicUrl = self::music_stream_url . $this->id . self::music_stream_url_sub;
                $this->coverImgUrl = self::MUSIC_COVER_IMG_URL_PREV .
                    substr($tmpMusicData[22], strlen($tmpMusicData[22]) - 2, 1)
                    . "/" . substr($tmpMusicData[22], strlen($tmpMusicData[22]) - 1, 1) . "/"
                    . $tmpMusicData[22] . ".jpg";
            } else {
                $this->error="对不起没有找到相关音乐,sorry~~";
            }
        }
    }
}