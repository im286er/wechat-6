<?php
/**
 * Created by PhpStorm.
 * User: xxp
 * Date: 2017/11/17
 * Time: 10:40
 */

namespace app\common\model;


class CurlT
{
    const timeout = 5;
    private $url = '';
    private $con;
    protected $data;
    public $error;

    public function __construct($url, $data)
    {
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function curl_get()
    {
        if (!empty($this->data)) {
            if (is_array($this->data)) {
                foreach ($this->data as $k => $v) {
                    $this->url .= '&' . $k . '=' . $v;
                }
            } else {
                $this->url .= $this->data;
            }
        }
        $this->con = curl_init($this->url);
        curl_setopt($this->con, CURLOPT_HEADER, false);
        curl_setopt($this->con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->con, CURLOPT_TIMEOUT, self::timeout);
        $res = curl_exec($this->con);
        if ($res == false)
            $this->error = curl_error($this->con);
        return $res;
    }

    /**
     * @return mixed
     */
    public function curl_post($type='')
    {
        $this->con = curl_init($this->url);
        switch (strtolower($type)) {
            case 'json':
                curl_setopt($this->con, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($this->data)))
                );
                curl_setopt($this->con, CURLOPT_POSTFIELDS, json_encode($this->data));
                break;
            default:
                curl_setopt($this->con, CURLOPT_POSTFIELDS, $this->data);
                break;
        }
        curl_setopt($this->con, CURLOPT_HEADER, false);
        curl_setopt($this->con, CURLOPT_POST, true);
        curl_setopt($this->con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->con, CURLOPT_TIMEOUT, self::timeout);
        $res = curl_exec($this->con);
        if ($res == false)
            $this->error = curl_error($this->con);
        return $res;
    }
}