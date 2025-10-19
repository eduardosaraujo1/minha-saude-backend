<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function getProfile()
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the user's name.
     */
    public function putName(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the user's birthdate.
     */
    public function putBirthdate(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the user's phone number.
     */
    public function putPhone(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Verify phone number with SMS code.
     */
    public function phoneVerify(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Send SMS verification code.
     */
    public function phoneSendSms(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Link Google account to user profile.
     */
    public function googleLink(Request $request)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Schedule user account deletion.
     */
    public function deleteProfile()
    {
        return response()->json(['status' => 'not_implemented']);
    }
}
