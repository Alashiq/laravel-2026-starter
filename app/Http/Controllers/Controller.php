<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function badRequest(String $message)
    {
        return response()->json(['success' => false, 'message' => $message], 400);
    }

    public function unauthorized(String $message)
    {
        return response()->json(['success' => false, 'message' => $message], 401);
    }

    public function empty()
    {
        return response()->json([], 204);
    }

    public function success(String $message)
    {
        return response()->json(['success' => true, 'message' => $message], 200);
    }
}
