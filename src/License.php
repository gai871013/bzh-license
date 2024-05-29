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
use Random\RandomException;

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
     * @throws RandomException
     */
    public function generate(array $data, string $private = ''): string
    {
        // 必要参数
        $field = config('license.field');
        if (!is_array($field)) {
            throw new InvalidArgumentException("Missing key field");
        }

        // 判断数据完整性
        foreach ($field as $key => $value) {
            if ($value && !isset($data[$key])) {
                throw new InvalidArgumentException("Missing key $key");
            }
        }

        if ($private === '') {
            $private = $this->private;
        }

        // 生成AES加密key
        $aes_key = bin2hex(openssl_random_pseudo_bytes(16));
        $key     = '';
        for ($i = 0; $i < strlen($aes_key); $i++) {
            if (random_int(1, 10) % 2 === 0) {
                $key .= $aes_key[$i];
            } else {
                $key .= strtoupper($aes_key[$i]);
            }
        }
        $aes_key = $key;

        // 加密信息
        $info = AES::encrypt(json_encode($data, JSON_UNESCAPED_UNICODE), $aes_key);

        // 加密信息长度
        $len = str_pad(dechex(strlen($info)), 4, '0', STR_PAD_LEFT);

        // 签名
        $sign = Rsa2::createSign($info, $private);

        return ($aes_key . $len . $info . $sign);
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

        if ($public === '') {
            $public = $this->public;
        }

        if (empty($public)) {
            throw new InvalidArgumentException('Public is empty');
        }

        // 原始信息
        // $license = base64_decode($license);
        // 密钥
        $aes_key = substr($license, 0, 32);
        // 加密信息长度
        $len = hexdec(substr($license, 32, 4));

        // 加密信息
        $info = substr($license, 36, $len);

        // 签名
        $sign = substr($license, 36 + $len);

        try {
            // 验证签名
            $check = Rsa2::verifySign($info, $sign, $public);
            if (!$check) {
                throw new Exception('授权码无效');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 解密
        $origin = AES::decrypt($info, $aes_key);
        // 解密后的信息
        $res = json_decode($origin, true);

        // 检测appid是否一致
        $appid        = config('license.appid');
        $res['valid'] = true;
        if ($appid !== $res['appid']) {
            $res['valid'] = false;
        }

        // 判断是否有效期
        $time = time();
        if (strtotime($res['notBefore']) > $time || strtotime($res['notAfter']) < $time) {
            $res['valid'] = false;
        }
        return $res;
    }

    /**
     * 检测授权是否有效
     * @param string $license
     * @param string $public
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function checkValid(string $license = '', string $public = ''): bool
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

        // 返回
        return $license['valid'];
    }

    /**
     * 授权是否临期
     * @param string $license
     * @param int $day 天数
     * @param string $public
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function checkAlert(string $license = '', int $day = 7, string $public = ''): bool
    {
        if (empty($license)) {
            return true;
        }
        try {
            $license = $this->getLicense($license, $public);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        // 无效直接返回临期
        if (!$license['valid']) {
            return true;
        }

        // 时间判断
        $time = time();
        if (strtotime($license['notAfter']) - $day * 86400 < $time) {
            return true;
        }
        return false;
    }

    /**
     * 生成Rsa公钥和私钥
     * @param int $private_key_bits
     * @return array
     */
    public function makeRsa(int $private_key_bits = 1024): array
    {
        return Rsa2::generate($private_key_bits);
    }
}
