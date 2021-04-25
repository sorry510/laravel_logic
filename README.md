## 使用 Logic 开发模式
>根据数据表自动生成对应的 controller, logic, model, validate 文件

- 命令

```
php artisan mvl -m 模块 -t 表名
```

- help

```
php artisan mvl -h
```

### 数据表要求
- 数据表的字段和注释分别自动生成对应的代码
- 枚举类型使用 enum 类型,注释标准编写如下，可以自动生成对应的常量和数据封装函数

```
状态[1:正常,2:禁止,3:未知]
```