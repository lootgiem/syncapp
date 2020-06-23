<?php


namespace App\Services\Connectors;


use App\Contracts\Services\Connectors\iOauthCodeConnector;
use App\Contracts\Services\iOauthClient;
use App\Models\Credential;

class OauthCodeConnector extends Connector implements iOauthCodeConnector
{
    /**
     * @var iOauthClient
     */
    protected iOauthClient $oauthClient;

    public function __construct(iOauthClient $oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    public function getOauthClient()
    {
        return $this->oauthClient->getClient();
    }

    public function getAuthUrl($token)
    {
        return $this->oauthClient->getAuthUrl($token);
    }

    public function getAccessTokenFromCode($code)
    {
        return $this->oauthClient->accessTokenFromCode($code);
    }

    public function isValidConnectionRequest($request)
    {
        return empty($request);
    }

    protected function connect(Credential $credential): Credential
    {
        $accessToken = false;

        if ($credential->secret && isset($credential->secret['access_token'])) {
            $accessToken = $this->oauthClient->updateAccessToken($credential->secret);

            if ($accessToken && isset($accessToken['access_token'])) {
                $credential = $credential->forceFill(['token' => null, 'secret' => $accessToken]);
            }

            $credential = $credential->forceFill(['valid' => true]);
        }

        if ($accessToken === false) {
            $credential->save();
            $redirectUrl = route('oauth.redirect-to-auth', ['credential' => $credential]);
            $credential = $credential->forceFill(['redirect' => $redirectUrl, 'valid' => false]);
        }

        $credential->save();
        return $credential;
    }
}


