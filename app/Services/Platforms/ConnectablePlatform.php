<?php


namespace App\Services\Platforms;


use App\Contracts\Services\Connectors\iConnector;
use App\Contracts\Services\CredentialsStrategies\iCredentialStrategy;
use App\Models\Credential;

abstract class ConnectablePlatform extends Platform
{
    /**
     * @var Credential
     */
    private Credential $credential;

    /**
     * @var iConnector
     */
    private iConnector $connector;

    /**
     * @var iCredentialStrategy
     */
    private iCredentialStrategy $credentialStrategy;

    public function __construct(iConnector $connector, iCredentialStrategy $credentialStrategy)
    {
        $this->connector = $connector;
        $this->credentialStrategy = $credentialStrategy;
    }

    public function setUser(Credential $credential)
    {
        $this->credential = $this->getConnector()->attemptConnection($credential);
        $this->onceConnected();
    }

    private function onceConnected()
    {
        if (method_exists($this, 'whenConnected')) {
            $this->whenConnected();
        }
    }

    protected function getCurrentCredential()
    {
        return $this->credential;
    }

    /**
     * @return iConnector
     */
    protected function getConnector()
    {
        return $this->connector;
    }

    /**
     * @return iCredentialStrategy
     */
    public function getCredentialStrategy()
    {
        return $this->credentialStrategy;
    }

    /**
     * @return array
     */
    public function getAgendas()
    {
        if (!$this->getCurrentCredential()->platform->has_agendas) {
            return array();
        }

        if (method_exists($this, 'retrieveAgendas')) {
            return $this->retrieveAgendas()->all();
        }
        else {
            throw new \LogicException('Connectable platform used but retrieveAgendas method not defined');
        }
    }
}
