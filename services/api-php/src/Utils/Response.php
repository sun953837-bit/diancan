<?php

namespace App\Utils;

class Response
{
    /**
     * 输出统一响应结构
     *
     * @param int $code 业务码
     * @param string $msg 提示信息
     * @param mixed $data 返回数据
     */
    public static function json(int $code, string $msg, $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $traceId = uniqid('trace_', true);
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'traceId' => $traceId,
        ], JSON_UNESCAPED_UNICODE);
    }
}
