<?php

/*
 * This file is part of the gai871013/bzh-license.
 *
 * (c) gai871013 <wang.gaichao@163.com>
 *
 * This source file is subject to the Apache license 2.0 that is bundled
 * with this source code in the file LICENSE.
 */

return [
    // 应用id
    'appid'   => env('LICENSE_APPID', 'domain.test'),
    // 公钥证书 支持路径&证书字符串
    'public'  => storage_path('cert/public.pem'),
    // 授权证书
    'key' => env('LICENSE_KEY', ''),
    // 授权信息字段
    'field'   => [
        'appid'          => true,   // 应用id
        'issuedTime'     => true,   // 发布时间
        'notBefore'      => true,   // 开始时间
        'notAfter'       => true,   // 结束时间
        'customerInfo'   => true,   // 公司名称
        'projectName'    => true,   // 项目名称
        'projectManager' => true,   // 项目经理信息
    ]
];
