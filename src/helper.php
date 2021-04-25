<?php

if (!function_exists('resJson')) {
    /**
     * http 响应 json 数据格式
     *
     * @Author sorry510 491559675@qq.com
     * @DateTime 2021-02-24
     *
     * @param int $code 自定义 code 码
     * @param string|array $msg 错误信息，如果不为''则认为是data字段数据
     * @param mixed $data 正常的数据
     * @param int $statusCode http code
     * @param array $headers 响应头设置
     * @return Response
     */
    function resJson(int $code, $msg = '', $data = null, int $statusCode = 200, array $headers = [])
    {
        /**
         * @var \App\Constants\ErrorCode
         */
        if ($msg === '') {
            // 默认填充标准错误信息
            $msg = getConstMessage($code);
        } else if (!is_string($msg)) {
            // 将msg改为data用，msg为充标准错误信息
            $data = $msg;
            $msg = getConstMessage($code);
        }
        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // 整理分页数据
            $data = [
                'meta' => [
                    'count' => $data->count(), // 当前页的项目数
                    'perPage' => $data->perPage(), // 每页显示的项目数
                    'currentPage' => $data->currentPage(), // 当前页码
                    'lastPage' => $data->lastPage(), // 最后一页的页码
                    'total' => $data->total(), // 总数
                ],
                'list' => $data->items(), // 数据
            ];
        }
        // // 设置响应结果
        // $status = 'success';
        // if ($statusCode >= 400 && $statusCode <= 499) {
        //     $status = 'error';
        // } else if ($statusCode >= 500 && $statusCode <= 599) {
        //     $status = 'fail';
        // }
        return response()->json([
            // "status" => $status, // 描述 HTTP 响应结果：HTTP 状态响应码在 500-599 之间为”fail”，在 400-499 之间为”error”，其它均为”success”
            "code" => (int) $code, // 自定义业务 code 码
            "message" => $msg,
            "data" => $data,
            "timestamp" => millisecond(),
        ], $statusCode, $headers, JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('millisecond')) {
    /**
     * 当前时间的毫秒级别时间戳
     * @return int
     */
    function millisecond()
    {
        return ceil(microtime(true) * 1000);
    }
}
