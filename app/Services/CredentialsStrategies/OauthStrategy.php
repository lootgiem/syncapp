<?php


namespace App\Services\CredentialsStrategies;


use App\Contracts\Services\Connectors\iOauthCodeConnector;
use App\Contracts\Services\CredentialsStrategies\iOauthCredentialStrategy;
use App\Models\Credential;
use App\Repositories\CredentialRepository;
use Illuminate\Support\Str;

class OauthStrategy extends CredentialStrategy implements iOauthCredentialStrategy
{
    public function __construct(iOauthCodeConnector $connector)
    {
        parent::__construct($connector);
    }

    public function getAuthUrl(Credential $credential)
    {
        $token = CredentialRepository::generateToken($credential);
        return $this->connector->getAuthUrl(Str::urlEncode($token));
    }

    public function generateAccessTokenFromCode(Credential $credential, string $code)
    {
        $accessToken = $this->connector->getAccessTokenFromCode($code);

        if (isset($accessToken['access_token'])) {
            $credential->forceFill([
                'token' => null,
                'redirect' => null,
                'secret' => $accessToken,
                'valid' => !$credential->platform->has_agendas
            ])->save();
        }
    }
}
