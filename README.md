## 优雅的使用常量命名

- 常量定义如下

```
<?php

declare (strict_types = 1);

namespace App\Constants;

class ErrorCode
{
    /**
     * @Message("操作成功")
     */
    const SUCCESS = 200;

    /**
     * @Message("操作失败")
     */
    const ERROR = 400;
}
```

## 安装

```
composer require sorry510/constant
```

#### 使用

- 生成demo文件

```
php artisan vendor:publish --tag=constant
```
>PS: 凡是在app/Constants 目录下的php文件，内部的所有常量，均会被注入到统一的常量列表数组中


- 获取信息

```
<?php

use App\Constants\ErrorCode

$message = getConstMessage(ErrorCode::SUCCESS); // 操作成功

```