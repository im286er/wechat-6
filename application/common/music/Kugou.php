<?php
/**
 * Created by PhpStorm.
 * User: Developer
 * Date: 2018/2/26
 * Time: 15:37
 */

namespace app\common\music;

use app\common\model\CurlT;

class Kugou
{
    const BASE_URL = "http://songsearch.kugou.com/song_search_v2";
    const CDN_MOBILE_URL = "http://mobilecdn.kugou.com/api/v3/search/song?format=json&showtype=1";
    public $keyword;
    public $page = 1;
    public $pagesize = 1;
    public $data = array();
    public $music = array();
    const MP3_URL = "http://www.kugou.com/yy/index.php?r=play/getdata";

    /**
     * Kugou constructor.
     * @param string $search_content
     */
    public function __construct($search_content = '')
    {
        if (!empty($search_content)) {
            $this->keyword = $search_content;
        }
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @param string $search_content
     * @return array
     */
    public function search($page = 1, $pageSize = 1, $search_content = '')
    {
        if (empty($search_content) && empty($this->keyword)) {
            header("");
            return array('status' => false, 'info' => 'you need key in search content.');
        }
        $this->keyword = !empty($search_content) ? $search_content : $this->keyword;
        $this->page = $page;
        $this->pagesize = $pageSize;
        $url = self::CDN_MOBILE_URL . "&keyword=" . rawurlencode($this->keyword) .
            "&page=$this->page&pagesize=$this->pagesize";
        $curl = new CurlT($url, '');
        $result = $curl->curl_get();
        if ($result !== false) {
            $data = json_decode($result, true);
            $this->data = $data['data']['info'];
            return $this->data;
        } else {
            return array('status' => $result, 'info' => $curl->error);
        }
    }

    /**
     * access public
     * @param string $Filehash
     * @param string $album_id
     * @return array
     */
    public function getMusic($Filehash = '', $album_id = '')
    {
        return $this->_getMusic($Filehash, $album_id);
    }

    /**
     * @param string $Filehash
     * @param string $album_id
     * @return array
     */
    protected function _getMusic($Filehash = '', $album_id = '')
    {
        if (empty($Filehash) || empty($album_id)) {
            return array();
        }
        $url = self::MP3_URL . "&hash=$Filehash&album_id=$album_id";
        $curl = new CurlT($url, '');
        $result = $curl->curl_get();
        if ($result !== false) {
            $data = json_decode($result, true);

            if ($data['status'] === 1) {
                return $data['data'];
            } else {
                return array();
            }
        } else {
            return array();
        }
    }
}


