<?php


namespace App\Services\Connectors;


use App\Contracts\Services\Connectors\iConnector;
use App\Exceptions\CredentialException;
use App\Models\Credential;

abstract class Connector implements iConnector
{
    /**
     * Return true if it is connected to the paltform.
     *
     * @param Credential $credential
     * @return Credential|null
     */
    abstract protected function connect(Credential $credential): ?Credential;

    /**
     * @param array $request
     * @return bool
     */
    abstract public function isValidConnectionRequest(array $request);

    public function attemptConnection($credential, $closure = null)
    {
        $credential = $this->connect($credential);

        if ($credential instanceof Credential) {
            if (is_callable($closure)) {
                call_user_func($closure);
            }
            return $credential;
        }

        throw CredentialException::wrongCredential();
    }
}
