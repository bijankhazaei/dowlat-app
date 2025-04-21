<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DevController extends Controller
{
    public function test(Request $request)
    {
        try {
           
        } catch (\Throwable $th) {
            return response()->apiResult($th->__toString());
        }

        return response()->apiResult(statusCode: 404);
    }
}
