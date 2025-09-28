<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show()
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the user's name.
     */
    public function updateName(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the user's birthdate.
     */
    public function updateBirthdate(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the user's phone number.
     */
    public function updatePhone(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Verify phone number with SMS code.
     */
    public function verifyPhone(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Send SMS verification code.
     */
    public function sendSms(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Link Google account to user profile.
     */
    public function linkGoogle(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Schedule user account deletion.
     */
    public function destroy()
    {
        return response()->json(['status' => 'not_implemented']);
    }
}
