<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    public function __construct()
    {
        if (Auth::guard('sanctum')->check()) {
        } else {
        }
    }
}
