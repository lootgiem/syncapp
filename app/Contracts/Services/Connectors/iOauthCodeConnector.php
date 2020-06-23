<?php


namespace App\Contracts\Services\Connectors;



interface iOauthCodeConnector extends iConnector
{
    /**
     * @return mixed
     */
    public function getOauthClient();

    /**
     * @param $token
     * @return mixed
     */
    public function getAuthUrl($token);

    /**
     * @param $code
     * @return mixed
     */
    public function getAccessTokenFromCode($code);
}
