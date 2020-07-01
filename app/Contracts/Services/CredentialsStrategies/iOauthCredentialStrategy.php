<?php


namespace App\Contracts\Services\CredentialsStrategies;


use App\Models\Credential;

interface iOauthCredentialStrategy extends iCredentialStrategy
{
    public function getAuthUrl(Credential $credential);

    public function generateAccessTokenFromCode(Credential $credential, string $code);

}
