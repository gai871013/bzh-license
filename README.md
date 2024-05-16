# 授权查询
- 主要功能：查询当前站点授权是否在有效期，并给于提示
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
