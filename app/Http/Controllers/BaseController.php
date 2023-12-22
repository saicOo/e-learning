<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($message, $data = null){
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'data' => $data,
            'message' => $message,
        ], 200);
    }

    public function sendError($error ,$errorMessages = [], $code = 404){
        return response()->json([
            'success' => false,
            'status_code' => $code,
            'message' => $error,
            'errors' => $errorMessages
        ], 200);
    }
}
