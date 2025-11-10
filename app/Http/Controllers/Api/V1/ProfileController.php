<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\DeleteRequest;
use App\Http\Requests\V1\UpdateBirthdateRequest;
use App\Http\Requests\V1\UpdateNameRequest;
use App\Http\Requests\V1\UpdatePhoneRequest;
use App\Modules\User\UserModule;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function getProfile(UserModule $userModule)
    {
        $result = $userModule->getUserInfo();

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        $profile = $result->getOrThrow();

        return response()->json($profile->toArray());
    }

    /**
     * Update the user's name.
     */
    public function putName(UpdateNameRequest $request, UserModule $userModule)
    {
        $validated = $request->validated();

        $result = $userModule->updateName($validated['nome']);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        return response()->json([]);
    }

    /**
     * Update the user's birthdate.
     */
    public function putBirthdate(UpdateBirthdateRequest $request, UserModule $userModule)
    {
        $validated = $request->validated();

        $result = $userModule->updateBirthdate($validated['dataNascimento']);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        return response()->json([]);
    }

    /**
     * Update the user's phone number.
     */
    public function putPhone(UpdatePhoneRequest $request, UserModule $userModule)
    {
        $validated = $request->validated();

        $result = $userModule->updatePhone($validated['telefone']);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        return response()->json([]);
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
    public function deleteProfile(DeleteRequest $request, UserModule $userModule)
    {
        $validated = $request->validated();

        $result = $userModule->requestDeletion($validated['reauthToken']);

        if ($result->isFailure()) {
            $error = $result->tryGetFailure();

            abort($error->code, $error->message);
        }

        return response()->json([]);
    }
}
