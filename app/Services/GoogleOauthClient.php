<?php

namespace App\Services;

use App\Contracts\Services\iOauthClient;
use Google_Client;
use Google_Exception;
use GuzzleHttp\Client;

class GoogleOauthClient implements iOauthClient
{
    private Google_Client $client;

    public function __construct($credential_path, $scope, $redirect_url, $httpClient)
    {
        $this->setClient($credential_path, $scope, $redirect_url, $httpClient);
    }

    /**
     * @param $credential_path
     * @param $scope
     * @param null $redirect_url
     * @param $httpClient
     * @return void
     * @throws Google_Exception
     */
    protected function setClient($credential_path, $scope, $redirect_url, $httpClient)
    {
        $client = new Google_Client();
        $client->setAuthConfig($credential_path);
        $client->addScope($scope);
        $client->setRedirectUri($redirect_url);
        $client->setPrompt("consent");
        $client->setAccessType("offline");
        $client->setIncludeGrantedScopes(true);
        $client->setRedirectUri($redirect_url);
        $client->setHttpClient($httpClient);
        $this->client = $client;
    }


    public function getAuthUrl(string $token)
    {
        $this->client->setState($token);
        return $this->client->createAuthUrl();
    }

    public function accessTokenFromCode(string $code)
    {
        $this->client->fetchAccessTokenWithAuthCode($code);
        return $this->client->getAccessToken();
    }

    public function updateAccessToken(array $secret)
    {
        $this->client->getCache()->clear();
        $this->client->setAccessToken($secret);

        if ($this->client->isAccessTokenExpired()) {
            return $this->refreshToken();
        }

        return true;
    }

    private function refreshToken()
    {
        if (!is_null($this->client->getRefreshToken())) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            return $this->client->getAccessToken();
        }

        return false;
    }

    /**
     * @return Google_Client
     */
    public function getClient() {
        return $this->client;
    }
}
