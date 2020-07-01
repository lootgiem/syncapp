<?php


namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class DoctolibApi
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var stdClass
     */
    protected StdClass $accountInformation;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->setAccountInformation();
    }

    public function setAccountInformation()
    {
        $url = config('platforms.list.doctolib.api_endpoint') . '/accounts';

        $response = $this->client->get($url, [
            'headers' => static::jsonHeaders()
        ]);

        $this->accountInformation = $this->fromJson($response);
    }

    public function getAccountInformation() {
        return $this->accountInformation;
    }

    public function addAbsence(array $event)
    {
        $url = config('platforms.list.doctolib.api_endpoint') . 'events';
        $response = $this->client->post($url, [
            'json' => $event
        ]);

        return collect($this->fromJson($response));
    }

    public function removeAbsence($eventId)
    {
        $url = config('platforms.list.doctolib.api_endpoint') . 'events/' . $eventId;
        $response = $this->client->delete($url, [
            'headers' => static::jsonHeaders()
        ]);

        return $this->fromJson($response);
    }

    public function getEvents(array $parameters)
    {
        $url = config('platforms.list.doctolib.api_endpoint') . '/events';

        $response = $this->client->get($url, [
            'headers' => static::jsonHeaders(),
            'query' => $parameters,
        ]);

        return collect($this->fromJson($response)->data);
    }

    function fromJson(ResponseInterface $response) {
        return json_decode($response->getBody()->getContents());
    }

    private static function jsonHeaders()
    {
        return ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
    }
}
