<?php

namespace Sorry510\Commands\Generate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Mvl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mvl {--m|module= : 模块名称}
        {--t|table= : 表名（不含模块名称）}
        {--k|pk=id : 主键名称，若对应表中存在主键，则为该主键}
        {--f|force=0 : 是否强制生成}
        {--F|create_file= : 指定生成哪些文件(m:model、c:controller、v:validate、l:logic)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '构建数据表对应的controller&logic&model&validate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = ucfirst(trim($this->option('module')));
        $this->table = trim($this->option('table'));
        $this->tableFormat = Str::of($this->table)->camel()->ucfirst();
        $this->pk = $pk = trim($this->option('pk'));
        $this->force = trim($this->option('force'));
        $createFile = trim($this->option('create_file'));
        if (!$this->module || !$this->table) {
            $this->error('module&table不能为空');
            return;
        }
        $columns = $this->getColumns($pk);

        // 模块名转驼峰
        if (strpos($this->module, "_") !== false) {
            $this->module = (string) Str::of($this->module)->camel()->ucfirst();
        }

        if ($this->force) {
            $this->info('开始强制覆盖！');
        }
        $step = 4;
        if ($createFile) {
            $step = 1;
        }
        $bar = $this->output->createProgressBar($step);
        if (!$createFile || $createFile == "c") {
            $this->createController($pk, $columns);
            $bar->advance();
        }
        if (!$createFile || $createFile == "l") {
            $this->createLogic($pk);
            $bar->advance();
        }
        if (!$createFile || $createFile == "m") {
            $this->createModel($columns);
            $bar->advance();
        }
        if (!$createFile || $createFile == "v") {
            $this->createValidate();
            $bar->advance();
        }
        $bar->finish();
        $this->info(' 文件创建完毕！');
    }

    /**
     * 构建controller文件
     *
     * @param string $pk
     * @return void
     */
    private function createController(string $pk, array $columns)
    {

        $path = app_path() . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $this->module;
        $this->makeDir($path);
        $fileName = $path . DIRECTORY_SEPARATOR . $this->tableFormat . 'Controller.php';
        if (file_exists($fileName) && !$this->force) {
            $this->warn("warning: Controllers\{$this->module}下已存在{$this->tableFormat}Controller.php");
            return;
        }
        if (file_exists(base_path('stubs/mvl/Controller.stub'))) {
            $stub = file_get_contents(base_path('stubs/mvl/Controller.stub'));
        } else {
            $stub = file_get_contents(__DIR__ . '/stubs/Controller.stub');
        }
        $this->replaceModule($stub)
            ->replaceTable($stub)
            ->replaceLowerTable($stub)
            ->replacePK($stub, $pk)
            ->replaceOA($stub, $columns);
        file_put_contents($fileName, $stub);
    }

    /**
     * 构建logic文件
     *
     * @param string $pk
     * @return void
     */
    private function createLogic(string $pk)
    {
        $path = app_path() . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Logic' . DIRECTORY_SEPARATOR . $this->module;
        $this->makeDir($path);
        $fileName = $path . DIRECTORY_SEPARATOR . $this->tableFormat . 'Logic.php';
        if (file_exists($fileName) && !$this->force) {
            $this->warn("warning: Logic\{$this->module}下已存在{$this->tableFormat}Logic.php");
            return;
        }
        if (file_exists(base_path('stubs/mvl/Logic.stub'))) {
            $stub = file_get_contents(base_path('stubs/mvl/Logic.stub'));
        } else {
            $stub = file_get_contents(__DIR__ . '/stubs/Logic.stub');
        }
        $this->replaceModule($stub)
            ->replaceTable($stub)
            ->replaceLowerTable($stub)
            ->replacePK($stub, $pk);
        file_put_contents($fileName, $stub);
    }

    /**
     * 构建model文件
     *
     * @return void
     */
    private function createModel(array $columns)
    {
        $path = app_path() . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $this->module;
        $this->makeDir($path);
        $fileName = $path . DIRECTORY_SEPARATOR . $this->tableFormat . 'Model.php';
        if (file_exists($fileName) && !$this->force) {
            $this->warn("warning: Models\{$this->module}下已存在{$this->tableFormat}Model.php");
            return;
        }
        if (file_exists(base_path('stubs/mvl/Model.stub'))) {
            $stub = file_get_contents(base_path('stubs/mvl/Model.stub'));
        } else {
            $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');
        }
        $this->replaceModule($stub)
            ->replaceTable($stub)
            ->replaceTableName($stub)
            ->replaceColumns($stub, $columns);
        file_put_contents($fileName, $stub);
    }

    /**
     * 构建validate文件
     *
     * @return void
     */
    private function createValidate()
    {
        $path = app_path() . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests' . DIRECTORY_SEPARATOR . $this->module;
        $this->makeDir($path);
        $fileName = $path . DIRECTORY_SEPARATOR . $this->tableFormat . 'Validate.php';
        if (file_exists($fileName) && !$this->force) {
            $this->warn("warning: Requests\{$this->module}下已存在{$this->tableFormat}Validate.php");
            return;
        }
        if (file_exists(base_path('stubs/mvl/Validate.stub'))) {
            $stub = file_get_contents(base_path('stubs/mvl/Validate.stub'));
        } else {
            $stub = file_get_contents(__DIR__ . '/stubs/Validate.stub');
        }
        $this->replaceModule($stub)
            ->replaceTable($stub);
        file_put_contents($fileName, $stub);
    }

    /**
     * 拼接真实表名
     *
     * @return string
     */
    private function getTableName(): string
    {
        // return Str::snake($this->module) . '_' . strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $this->table));
        $tableName = config('database.connections.mysql.prefix') . Str::snake($this->module) . '_' . strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $this->table));
        $m_re = "";
        try {
            $m_re = DB::select("SHOW FULL COLUMNS FROM `{$tableName}`");
        } catch (\Throwable $e) {
            // echo "{$tableName}不存在" . PHP_EOL;
        }
        if (!$m_re) {
            return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $this->table));
        }
        return Str::snake($this->module) . '_' . strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $this->table));
    }

    /**
     * 获取数据表字段
     *
     * @param string $pk
     * @return array
     */
    private function getColumns(string &$pk): array
    {
        try {
            $option = ["field", "type", "comment", "key"];
            $tableName = config('database.connections.mysql.prefix') . $this->getTableName();
            $columns = array();
            $m_re = DB::select("SHOW FULL COLUMNS FROM `{$tableName}`");
            if (!$m_re) {
                return array();
            }
            foreach ($m_re as $v) {
                $v = array_change_key_case(json_decode(json_encode($v), true));
                $vv = array();
                foreach ($option as $op) {
                    $op = strtolower($op);
                    if (array_key_exists($op, $v)) {
                        $vv[$op] = $v[$op];
                    }
                }
                if ($v["key"] == "PRI") {
                    $pk = $v["field"];
                }
                $columns[$v["field"]] = $vv;
            }
            return $columns;
        } catch (\Throwable $e) {
            echo "warning: " . $e->getMessage() . PHP_EOL;
            return [];
        }
    }

    /**
     * 创建目录
     *
     * @param string $path
     * @return void
     */
    private function makeDir(string $path): void
    {
        if (!file_exists($path)) {
            @mkdir($path, 0755, true);
        }
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @return self
     */
    private function replaceModule(string &$stub): self
    {
        $stub = str_replace(
            ['%MODULE%'],
            [$this->module],
            $stub
        );
        return $this;
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @return self
     */
    private function replaceTable(string &$stub): self
    {
        $stub = str_replace(
            ['%TABLE%'],
            [$this->tableFormat],
            $stub
        );
        return $this;
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @return self
     */
    private function replaceLowerTable(string &$stub): self
    {
        $stub = str_replace(
            ['%LTABLE%'],
            [lcfirst($this->tableFormat)],
            $stub
        );
        return $this;
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @return self
     */
    private function replaceTableName(string &$stub): self
    {
        $stub = str_replace(
            ['%TABLENAME%'],
            [$this->getTableName()],
            $stub
        );
        return $this;
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @param string $pk
     * @return self
     */
    private function replacePK(string &$stub, string $pk): self
    {
        $stub = str_replace(
            ['%PK%'],
            [$pk],
            $stub
        );
        return $this;
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @param string $pk
     * @return self
     */
    private function replaceColumns(string &$stub, array $columns): self
    {
        $namespace = $class = $toFixed = $dateDeal = $const = $append = $func = $castsAttr = '';
        if (empty($columns)) {
            $str = '';
            $create = "";
            $update = "";
        } else {
            $str = "/**\r\n";
            $str .= " * App\Models\\{$this->module}\\{$this->tableFormat}Model\r\n *\r\n";
            $whereStr = '';
            $create = "    const CREATED_AT = null;\r\n";
            $update = "    const UPDATED_AT = null;\r\n";
            foreach ($columns as $v) {
                $type = $this->toPHPType($v['type']);
                $str .= " * @property {$type} \${$v['field']} {$v['comment']}\r\n";

                if ($v['field'] == "delete_time") {
                    $namespace = "use Illuminate\Database\Eloquent\SoftDeletes;\r\n";
                    $class = "    use SoftDeletes;\r\n";
                }

                if ($v['field'] == "create_time") {
                    $create = "";
                }
                if ($v['field'] == "update_time") {
                    $update = "";
                }

                $field = ucfirst($this->toCamelCase($v['field']));
                // 添加 where 方法注释
                $whereStr .= " * @method static \Illuminate\Database\Eloquent\Builder|{$this->tableFormat}Model where{$field}(\$value)\r\n";
                if ($type == "float") {
                    $toFixed .= <<<EOT
    public function get{$field}Attribute(\$value)
    {
        return sprintf("%.2f",\$value);
    }\r\n
EOT;
                }

//                 if (strpos($v['field'], 'time') !== false && $v['field'] != "create_time" && $v['field'] != "update_time" && $v['field'] != "delete_time") {
                //                     $dateDeal .= <<<EOT
                //     public function set{$field}Attribute(\$value)
                //     {
                //         \$this->attributes['{$v['field']}'] = time_format(\$value, "Y-m-d H:i:s");
                //     }\r\n
                //     public function get{$field}Attribute(\$value)
                //     {
                //         return \$value ? time_format(strtotime(\$value), "Y-m-d H:i:s") : '';
                //     }\r\n
                // EOT;
                //                 }

                if (strpos($v['type'], 'enum') !== false) {
                    $comment = $this->getEnumComment($v['comment']);
                    if (!empty($comment)) {
                        $this->setEnumCode($comment, $v['field'], $const, $append, $func);
                        $str .= " * @property-read string \${$v['field']}_text\r\n";
                    }
                }

                if (strpos($v['field'], 'json') !== false) {
                    $castsAttr .= "'{$v['field']}' => 'array', ";
                }
            }
            // $str .= " * @method static \Illuminate\Database\Eloquent\Builder|{$this->tableFormat}Model newModelQuery()\r\n";
            // $str .= " * @method static \Illuminate\Database\Eloquent\Builder|{$this->tableFormat}Model newQuery()\r\n";
            // $str .= " * @method static \Illuminate\Database\Query\Builder|{$this->tableFormat}Model onlyTrashed()\r\n";
            // $str .= " * @method static \Illuminate\Database\Eloquent\Builder|{$this->tableFormat}Model query()\r\n";
            $str .= $whereStr;
            // $str .= " * @method static \Illuminate\Database\Query\Builder|{$this->tableFormat}Model withTrashed()\r\n";
            // $str .= " * @method static \Illuminate\Database\Query\Builder|{$this->tableFormat}Model withoutTrashed()\r\n";
            $str .= " * @mixin \Eloquent\r\n";
            $str .= " */";
        }
        // 字段注释
        $stub = str_replace(
            ['%COLUMNS%'],
            [$str],
            $stub
        );
        // 软删除
        $stub = str_replace(
            ['%SOFTDELETE_NAMESPACE%'],
            [$namespace],
            $stub
        );
        $stub = str_replace(
            ['%SOFTDELETE_CLASS%'],
            [$class],
            $stub
        );
        // 自动完成时间
        $stub = str_replace(
            ['%CREATETIME%'],
            [$create],
            $stub
        );
        $stub = str_replace(
            ['%UPDATETIME%'],
            [$update],
            $stub
        );
        // 浮点型保留两位小数
        $stub = str_replace(
            ['%TOFIXED%'],
            [$toFixed],
            $stub
        );
        // 日期格式化
        $stub = str_replace(
            ['%DATEDEAL%'],
            [$dateDeal],
            $stub
        );
        //枚举类型
        $stub = str_replace(
            ['%CONST%'],
            [$const],
            $stub
        );
        $stub = str_replace(
            ['%APPEND%'],
            [$append],
            $stub
        );
        $stub = str_replace(
            ['%FUNC%'],
            [$func],
            $stub
        );
        $stub = str_replace(
            ['%CASTS%'],
            [$castsAttr],
            $stub
        );
        return $this;
    }

    /**
     * 模板变量替换
     *
     * @param string $stub
     * @param string $pk
     * @return self
     */
    private function replaceOA(string &$stub, array $columns): self
    {
        if (empty($columns)) {
            $rsStr2 = $resStr = $reqStr = '     *                 @OA\Property(property="", type="integer", description=""),';
        } else {
            $rsStr2 = $resStr = $reqStr = '';
            foreach ($columns as $v) {
                if ($v['key'] == "PRI") {
                    continue;
                }
                $type = $this->toSWGType($v['type']);
                if ($v['field'] == "delete_time") {
                    continue;
                }

                if ($v['field'] == "create_time" || $v['field'] == "update_time") {
                    $resStr .= "     *             @OA\Property(property=\"{$v['field']}\", type=\"string\", description=\"{$v['comment']}\"),\r\n";
                    $rsStr2 .= "     *                 @OA\Property(property=\"{$v['field']}\", type=\"string\", description=\"{$v['comment']}\"),\r\n";
                } else {
                    if (Str::endsWith($v['field'], 'time')) {
                        $type = 'string';
                    }
                    $reqStr .= "     *                 @OA\Property(property=\"{$v['field']}\", type=\"{$type}\", description=\"{$v['comment']}\"),\r\n";
                    $resStr .= "     *             @OA\Property(property=\"{$v['field']}\", type=\"{$type}\", description=\"{$v['comment']}\"),\r\n";
                    $rsStr2 .= "     *                 @OA\Property(property=\"{$v['field']}\", type=\"{$type}\", description=\"{$v['comment']}\"),\r\n";
                }
            }

        }
        $stub = str_replace(
            ['%REQDATA%'],
            [trim($reqStr, "\r\n")],
            $stub
        );
        $stub = str_replace(
            ['%RESDATA%'],
            [trim($resStr, "\r\n")],
            $stub
        );
        $stub = str_replace(
            ['%RESDATA2%'],
            [trim($rsStr2, "\r\n")],
            $stub
        );
        return $this;
    }

    /**
     * 数据表字段类型转换为php类型
     *
     * @param string $type
     * @return string
     */
    private function toPHPType(string $type): string
    {
        if (strpos($type, 'int') !== false) {
            return 'int';
        }
        if (strpos($type, 'double') !== false || strpos($type, 'float') !== false) {
            return 'float';
        }
        if (strpos($type, 'json') !== false) {
            return 'array';
        }
        return 'string';
    }

    /**
     * 数据表字段类型转换为php类型
     *
     * @param string $type
     * @return string
     */
    private function toSWGType(string $type): string
    {
        if (strpos($type, 'int') !== false) {
            return 'integer';
        }
        if (strpos($type, 'double') !== false || strpos($type, 'float') !== false || strpos($type, 'decimal') !== false) {
            return 'number';
        }
        return 'string';
    }

    /**
     * 下划线命名到驼峰命名
     *
     * @param string $str
     * @return string
     */
    private function toCamelCase(string $str): string
    {
        $array = explode('_', $str);
        $result = $array[0];
        $len = count($array);
        if ($len > 1) {
            for ($i = 1; $i < $len; $i++) {
                $result .= ucfirst($array[$i]);
            }
        }
        return $result;
    }

    /**
     * 解析枚举类型的注释
     *
     * @param string $comment
     * @return array
     */
    private function getEnumComment(string $comment): array
    {
        try {
            preg_match('/\[(.*)\]/', $comment, $info);
            $arr = explode(",", $info[1]);
            $newArr = [];
            foreach ($arr as $v) {
                $a = explode(':', $v);
                $newArr[$a[0]] = $a[1];
            }
            return $newArr;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 生成枚举字段相关代码
     *
     * @param array $comment
     * @param string $field
     * @param string $const
     * @param string $append
     * @param string $func
     * @return void
     */
    private function setEnumCode(array $comment, string $field, string &$const, string &$append, string &$func)
    {
        $upField = strtoupper($field);
        $cameCaseField = ucfirst($this->toCamelCase($field));
        $arr = '';
        $arrJson = '';
        foreach ($comment as $key => $val) {
            $const .= <<<EOT
    /**
     * @message {$val}
     */
    const {$upField}_{$key} = '{$key}';\r\n
EOT;

            // $const .= "    const {$upField}_{$key} = '{$key}';\r\n";
            $arr .= "self::{$upField}_{$key} => '{$val}',\r\n            ";
            $arrJson .= "['key' => self::{$upField}_{$key}, 'val' => '{$val}'],\r\n            ";
        }
        $arr = trim($arr);
        $arrJson = trim($arrJson);
        $append .= "'{$field}_text', ";
        $func .= <<<EOT
    public function get{$cameCaseField}List()
    {
        return [
            {$arr}
        ];
    }\r\n
    public function get{$cameCaseField}ListJson()
    {
        return [
            $arrJson
        ];
    }\r\n
    public function get{$cameCaseField}TextAttribute()
    {
        return isset(\$this->{$field}) ? \$this->get{$cameCaseField}List()[\$this->{$field}] : '';
    }\r\n
EOT;
    }
}
