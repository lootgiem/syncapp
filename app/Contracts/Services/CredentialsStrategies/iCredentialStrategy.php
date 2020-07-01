<?php


namespace App\Contracts\Services\CredentialsStrategies;


use App\Http\Requests\StoreCredentialRequest;
use App\Http\Requests\UpdateCredentialRequest;
use App\Models\Credential;

interface iCredentialStrategy
{
    public function createCredential(StoreCredentialRequest $request);

    public function updateCredential(UpdateCredentialRequest $request, Credential $credential);

    public function deleteCredential(Credential $credential);
}
