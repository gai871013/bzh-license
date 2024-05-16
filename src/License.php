<?php
/**
 * 查询 & 生成授权
 * Author: @gai871013
 * Date: 2024-05-15 甲辰[龙年]四月初八
 * Time: UTC/GMT+08:00 18:24
 * src/AES.php
 */

namespace Gai871013\License;

use Gai871013\License\Exceptions\Exception;
use Gai871013\License\Exceptions\InvalidArgumentException;

class License
{
    public string $public;
    public string $private;

    public function __construct($public = '', $private = '')
    {
        if ($private) {
            $this->private = $private;
        } else {
            $this->private = config("license.private");
        }

        if ($public) {
            $this->public = $public;
        } else {
            $this->public = config('license.public');
        }
        try {
            $this->setPrivate($this->private);
        } catch (InvalidArgumentException $e) {
        }
        try {
            $this->setPublic($this->public);
        } catch (InvalidArgumentException $e) {
        }
    }

    /**
     * 设置私钥
     * @param $private
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPrivate($private): static
    {
        if (file_exists($private)) {
            $this->private = file_get_contents($private);
        } else {
            $this->private = $private;
        }

        try {
            openssl_pkey_get_private($this->private);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Private key provided does not exist");
        }

        return $this;
    }

    /**
     * 设置公钥
     * @param $public
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPublic($public): static
    {
        if (file_exists($public)) {
            $this->public = file_get_contents($public);
        } else {
            $this->public = $public;
        }

        try {
            openssl_pkey_get_public($this->public);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Public key provided does not exist");
        }

        return $this;
    }

    /**
     * 生成授权信息
     * @param array $data
     * @param string $private
     * @return string
     * @throws InvalidArgumentException
     */
    public function generate(array $data, string $private = ''): string
    {
        // 必要参数
        $field = config('license.field');
        if(!is_array($field)) {
            throw new InvalidArgumentException("Missing key field");
        }

        // 判断数据完整性
        foreach ($field as $key => $value) {
            if ($value && !isset($data[$key])) {
                throw new InvalidArgumentException("Missing key $key");
            }
        }

        if($private === '') {
            $private = $this->private;
        }

        // 生成AES加密key
        $aes_key = bin2hex(openssl_random_pseudo_bytes(16));

        // 加密信息
        $info = AES::encrypt(json_encode($data, JSON_UNESCAPED_UNICODE), $aes_key);

        // 加密信息长度
        $len = str_pad(dechex(strlen($info)), 3, '0', STR_PAD_LEFT);

        // 签名
        $sign = Rsa2::createSign($info, $private);

        return base64_encode($aes_key . $len . $info . $sign);
    }

    /**
     * 查询授权
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getLicense($license = '', $public = ''): array
    {

        if (empty($license)) {
            throw new InvalidArgumentException('License is empty');
        }

        if($public === '') {
            $public = $this->public;
        }

        if (empty($public)) {
            throw new InvalidArgumentException('Public is empty');
        }

        // 原始信息
        $license = base64_decode($license);
        // 密钥
        $aes_key = substr($license, 0, 32);
        // 加密信息长度
        $len = hexdec(substr($license, 32, 3));

        // 加密信息
        $info = substr($license, 35, $len);

        // 签名
        $sign = substr($license, 35 + $len);

        try{
            // 验证签名
            $check = Rsa2::verifySign($info, $sign, $public);
            if(!$check) {
                throw new Exception('授权码无效');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 解密
        $origin = AES::decrypt($info, $aes_key);
        // 解密后的信息
        return json_decode($origin, true);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function checkValid($license = '', $public = ''): bool
    {
        if (empty($license)) {
            return false;
        }
        try {
            $license = $this->getLicense($license, $public);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        // 判断是否有效期
        $time = time();
        if (strtotime($license['notBefore']) > $time || strtotime($license['notAfter']) < $time) {
            return false;
        } else {
            return true;
        }
    }
}
