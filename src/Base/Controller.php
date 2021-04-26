<?php

namespace App\Base;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function resJson(int $code, $msg = '', $data = null, int $statusCode = 200, array $headers = [])
    {
        return resJson($code, $msg, $data, $statusCode, $headers);
    }
}
