# 授权生成&查询
- 主要功能
    - 授权码生成
    - 查询是授权码
    - 生成 RSA 公私钥
- 测试环境：Laravel 8.0+
- 最后更新时间：2024年05月16日

## 安装

```shell
# 安装
composer require gai871013/bzh-license
# 发布默认证书及设置，仅作为测试使用，不得用于生产
php artisan vendor:publish --provider="Gai871013\License\ServiceProvider"
# 强制发布
php artisan vendor:publish --provider="Gai871013\License\ServiceProvider" --force
# 
```

## 使用方式
### 在Laravel中使用
1.在 `config/app.php` 注册 ServiceProvider 和 Facade (Laravel 5.5 + 无需手动注册)
```php
<?php
['providers' => [
    // ...
    Gai871013\License\ServiceProvider::class,
],
'aliases' => [
    // ...
    'License' => Gai871013\License\Facades\License::class,
],
];
```

2.使用：

```php
<?php
use Gai871013\License\Facades\License;

// ...
dump(app('License')->getLicense('LICENSE_CODE', 'PUBLIC_PEM_CODE'));
dd(License::getLicense('LICENSE_CODE', 'PUBLIC_PEM_CODE'));
[
    "appid" => "domain.test",
    "issuedTime" => "2024-05-14 16:30:00",
    "notBefore" => "2024-05-01 00:00:00",
    "notAfter" => "2025-04-30 23:59:59",
    "customerInfo" => "阿里巴巴集团",
    "projectName" => "项目名称",
    "valid" => true
];
// ...

```
## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/gai871013/bzh-license/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/gai871013/bzh-license/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._


## LICENSE
Apache License 2.0


Copyright [gai871013](https://github.com/gai871013/bzh-license)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

