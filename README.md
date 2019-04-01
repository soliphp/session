Soli PHP Web
----------------

Soli Web Component.

## 安装

使用 `composer` 进行安装：

    composer require soliphp/web

## 使用

如下我们编写 `index.php` 文件，内容为：

```php
<?php

// 默认控制器命名空间
namespace App\Controllers;

include __DIR__ . "/vendor/autoload.php";

class IndexController extends \Soli\Controller
{
    public function index()
    {
        return 'Hello, Soli.';
    }
}

$app = new \Soli\Web\App();

// 处理请求，输出响应内容
$app->handle()->send();

$app->terminate();
```

1. 使用 php -S 启动 webserver

```
php -S localhost:8000
```

2. 浏览器访问 `http://localhost:8000/`，即可看到如下信息：

```
Hello, Soli.
```


## License

[MIT License]


[MIT License]: LICENSE
