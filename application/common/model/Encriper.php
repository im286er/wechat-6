<?php
/**
 * Created by PhpStorm.
 * User: xianghua_we
 * Date: 2017/8/28 0028
 * Time: 11:33
 */

namespace app\common\model;


class Encriper
{
    private static $key = '7s35IoWhPF0TFWld';
    private static $signKey = '2haCNw6UB41P78A4';

    /**
     * 签名 php sha256 Java  HmacSHA256
     * @param String content 签名内容
     * @return hex
     */
    public static function sign($content)
    {
        return strtoupper(hash_hmac('sha256', $content, self::$signKey));
    }

    /**
     * 验签
     * @param content    签名内容
     * @param sign        待验签名
     * @return            true：合法； false：非法
     * @throws Exception
     */
    public static function verify($content, $sign)
    {

        if ($sign == self::sign($content)) {
            return true;
        }
        return false;

    }

    /**
     * 加密
     * @param String input 加密的字符串
     * @param String key   解密的key
     * @return HexString
     */
    public static function encrypt($input)
    {

        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, self::$key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = bin2hex($data);
        return $data;

    }

    /**
     * 填充方式 pkcs5
     * @param String text         原始字符串
     * @param String blocksize   加密长度
     * @return String
     */
    private static function pkcs5_pad($text, $blocksize)
    {

        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);

    }

    /**
     * 解密
     * @param String input 解密的字符串
     * @param String key   解密的key
     * @return String
     */
    public static function decrypt($sStr)
    {
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::$key, hex2bin($sStr), MCRYPT_MODE_ECB);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s - 1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }

    /**
     * key=value&key1=value1
     * @param $para 数组
     * @param $encode 是否需要URL编码
     * @return string
     */
    private static function createLinkString($para, $encode)
    {

        ksort($para);   //排序
        $linkString = "";
        while (list ($key, $value) = each($para)) {
            if ($encode) {
                $value = urlencode($value);
            }
            $linkString .= $key . "=" . $value . "&";
        }
        // 去掉最后一个&字符
        $linkString = substr($linkString, 0, count($linkString) - 2);
        return $linkString;

    }
}