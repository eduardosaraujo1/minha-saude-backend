<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Handle user login with Google
     */
    public function loginWithGoogle(Request $request)
    {
        throw new \Exception("Class not implemented");
    }

    /**
     * Handle user login with Email
     */
    public function loginWithEmail(Request $request)
    {
        throw new \Exception("Class not implemented");
    }

    /**
     * Send e-mail code for login
     */
    public function sendEmailCode(Request $request)
    {
        throw new \Exception("Class not implemented");
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        throw new \Exception("Class not implemented");
    }
}
