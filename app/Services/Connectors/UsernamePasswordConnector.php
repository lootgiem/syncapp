<?php


namespace App\Services\Connectors;


use App\Models\Credential;
use Exception;
use GuzzleHttp\Client;

class UsernamePasswordConnector extends Connector
{
    /**
     * @var string
     */
    protected string $loginUrl;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @var Client
     */
    protected Client $httpClient;

    public function __construct(Client $httpClient, $loginUrl, $data = [])
    {
        $this->loginUrl = $loginUrl;
        $this->data = $data;
        $this->httpClient = $httpClient;
    }

    public function getHttpClient() {
        return $this->httpClient;
    }

    public function isValidConnectionRequest(array $request)
    {
        return array_key_exists('username', $request) && array_key_exists('password', $request);
    }

    /**
     * @param Credential $credential
     * @return Credential|null ?Credential
     */
    protected function connect(Credential $credential): ?Credential
    {
        $secret = $credential->secret;

        $data = array_merge($data = [
            'username' => $secret['username'],
            'password' => $secret['password']
        ], $this->data);

        try {
            $response = $this->httpClient->post($this->loginUrl, [
                'json' => $data
            ]);
        } catch (Exception $e) {
            return null;
        }

        if ($response->getStatusCode() == 200) {
            $credential = $credential->forceFill(['valid' => true]);
            $credential->save();
            return $credential;
        }

        return null;
    }
}
