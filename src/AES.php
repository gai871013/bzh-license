<?php
/**
 * AES 加解密
 * Author: @gai871013
 * Date: 2024-05-15 甲辰[龙年]四月初八
 * Time: UTC/GMT+08:00 18:24
 * src/AES.php
 */

namespace Gai871013\License;

class AES
{
    /**
     * 加密
     * @param $input
     * @param $key
     * @param string $method
     * @return string   加密后的数据
     */
    public static function encrypt($input, $key, string $method = 'AES-256-ECB'): string
    {
        $data = openssl_encrypt($input, $method, $key, OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    /**
     * 解密
     * @param $sStr
     * @param $sKey
     * @param string $method
     * @return string        解密后的数据
     */
    public static function decrypt($sStr, $sKey, string $method = 'AES-256-ECB'): string
    {
        return openssl_decrypt(base64_decode($sStr), $method, $sKey, OPENSSL_RAW_DATA);
    }


    /**
     * @desc DESCRIPTION
     * @param string $string 需要加密的字符串
     * @param string $key 密钥
     * @param string $method
     * @return string
     * @author 王改朝 gai871013 <wanggaichao@gmail.com>
     * @created 2019-10-23T15:17:31.000+8:00
     */
    public static function java_encrypt(string $string, string $key, string $method = 'AES-256-ECB'): string
    {
        // 对接java，服务商做的AES加密通过SHA1PRNG算法（只要password一样，每次生成的数组都是一样的），Java的加密源码翻译php如下：
        // $key = substr(openssl_digest(openssl_digest($key, 'sha1', true), 'sha1', true), 0, 16);
        // openssl_encrypt 加密不同Mcrypt，对秘钥长度要求，超出16加密结果不变
        $data = openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA);
        $data = strtoupper(bin2hex($data));
        return base64_encode($data);
    }


}
