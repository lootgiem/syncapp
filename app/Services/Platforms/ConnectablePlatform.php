<?php


namespace App\Services\Platforms;


use App\Contracts\Services\Connectors\iConnector;
use App\Models\Credential;
use App\Services\CredentialsStrategies\CredentialStrategy;

trait ConnectablePlatform
{
    protected Credential $credential;

    public function setUser(Credential $credential)
    {
        $this->credential = $this->getConnector()->attemptConnection($credential);
        $this->onceConnected();
    }

    public function onceConnected()
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
    public function getConnector()
    {
        if (property_exists($this, 'connector')) {
            return $this->connector;
        }
        else {
            throw new \LogicException('Connectable trait is used but connector attribute is not set.');
        }
    }

    /**
     * @return CredentialStrategy
     */
    public function getCredentialStrategy()
    {
        if (property_exists($this, 'credentialStrategy')) {
            return $this->credentialStrategy;
        }
        else {
            throw new \LogicException('Connectable trait require credential strategy to be used.');
        }
    }
}
