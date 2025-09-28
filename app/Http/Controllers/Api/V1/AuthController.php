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
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Handle user login with Email
     */
    public function loginWithEmail(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }


    /**
     * Send e-mail code for login
     */
    public function sendEmail(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }
}
