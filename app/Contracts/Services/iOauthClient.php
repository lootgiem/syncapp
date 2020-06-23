<?php


namespace App\Contracts\Services;



interface iOauthClient
{
    public function getClient();

    public function accessTokenFromCode(string $code);

    public function getAuthUrl(string $token);

    public function updateAccessToken(array $secret);
}
