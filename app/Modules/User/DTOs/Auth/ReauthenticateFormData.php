<?php

namespace App\Modules\User\DTOs\Auth;

readonly class ReauthenticateFormData
{
    public function __construct(
        public string $authType,
        public ?GoogleAuthData $googleAuth,
        public ?EmailAuthData $emailAuth
    ) {}

    public static function fromRequest(array $data): self
    {
        $auth = $data['auth'];

        $authType = null;
        $googleAuth = null;
        $emailAuth = null;

        if (isset($auth['google'])) {
            $googleAuth = new GoogleAuthData($auth['google']['oauthToken']);
            $authType = 'google';
        }

        if (isset($auth['email'])) {
            $emailAuth = new EmailAuthData(
                $auth['email']['email'],
                $auth['email']['code']
            );
            $authType = 'email';
        }

        return new self($authType, $googleAuth, $emailAuth);
    }
}

readonly class GoogleAuthData
{
    public function __construct(
        public string $oauthToken
    ) {}
}

readonly class EmailAuthData
{
    public function __construct(
        public string $email,
        public string $code
    ) {}
}
