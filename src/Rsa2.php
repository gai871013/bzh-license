<?php
/**
 * Rsa256签名和验签
 * Author: @gai871013
 * Date: 2024-05-15 甲辰[龙年]四月初八
 * Time: UTC/GMT+08:00 15:50
 * src/Rsa2.php
 */

namespace Gai871013\License;

class Rsa2
{
    /**
     * 获取私钥
     * @param string $private
     * @return false|\OpenSSLAsymmetricKey|void
     */
    private static function getPrivateKey(string $private = '')
    {
        try {
            $cer = trim($private);
            if (!str_contains($cer, '-----BEGIN RSA PRIVATE KEY-----')) {
                $cer = "-----BEGIN RSA PRIVATE KEY-----\n" .
                       chunk_split($cer, 64, PHP_EOL) .
                       "-----END RSA PRIVATE KEY-----\n";
            }
            return openssl_pkey_get_private($cer);
        } catch (\Exception $exception) {
            exit(sprintf('请求参数错误[%s]', $exception->getMessage()));
        }
    }

    /**
     * 获取公钥
     * @param string $public
     * @return false|\OpenSSLAsymmetricKey|void
     */
    private static function getPublicKey(string $public = '')
    {
        try {
            $cer = trim($public);
            if (!str_contains($cer, '-----BEGIN PUBLIC KEY-----')) {
                $cer = "-----BEGIN PUBLIC KEY-----\n" .
                       chunk_split($cer, 64, PHP_EOL) .
                       "-----END PUBLIC KEY-----\n";
            }
            return openssl_pkey_get_public($cer);
        } catch (\Exception $exception) {
            exit(sprintf('请求参数错误[%s]', $exception->getMessage()));
        }
    }

    /**
     * 创建签名
     * @param string $data 数据
     * @param string $private
     * @return null|string
     */
    public static function createSign(string $data = '', string $private = ''): ?string
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_sign(
            $data,
            $sign,
            self::getPrivateKey($private),
            OPENSSL_ALGO_SHA256
        ) ? base64_encode($sign) : null;
    }

    /**
     * 验证签名
     * @param string $data 数据
     * @param string $sign 签名
     * @param string $public
     * @return bool
     */
    public static function verifySign(string $data = '', string $sign = '', string $public = ''): bool
    {
        if (!is_string($sign)) {
            return false;
        }
        return (bool)openssl_verify(
            $data,
            base64_decode($sign),
            self::getPublicKey($public),
            OPENSSL_ALGO_SHA256
        );
    }


    /**
     * 生成Rsa公钥和私钥
     * @param string $path
     * @param int $private_key_bits 建议：[512, 1024, 2048, 4096]
     * @return array
     */
    public static function generate(int $private_key_bits = 1024): array
    {
        $rsa = [];

        $config = [
            "digest_alg"       => OPENSSL_ALGO_SHA256,
            "private_key_bits" => $private_key_bits, #此处必须为int类型
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        //创建公钥和私钥
        $res = openssl_pkey_new($config);

        //提取私钥
        openssl_pkey_export($res, $rsa['private']);

        $rsa['private'] = str_replace('PRIVATE KEY', 'RSA PRIVATE KEY', $rsa['private']);

        //生成公钥
        $rsa['public'] = openssl_pkey_get_details($res)["key"];
        return $rsa;
    }

}
