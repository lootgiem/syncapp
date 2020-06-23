<?php


namespace App\Contracts\Services\Connectors;


use App\Models\Credential;

interface iConnector
{
    /**
     * Return the credential if it is connected to the paltform.
     *
     * @param Credential $credential
     * @param null $closure
     * @return Credential
     */
    public function attemptConnection(Credential $credential, $closure = null);

    /**
     * Is a valid connection request
     *
     * @param array $request
     * @return bool
     */
    public function isValidConnectionRequest(array $request);
}
