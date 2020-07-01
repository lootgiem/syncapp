<?php


namespace App\Services\CredentialsStrategies;


use App\Contracts\Services\Connectors\iConnector;
use App\Contracts\Services\CredentialsStrategies\iCredentialStrategy;
use App\Events\CredentialToDesynchronize;
use App\Exceptions\CredentialException;
use App\Http\Requests\StoreCredentialRequest;
use App\Http\Requests\UpdateCredentialRequest;
use App\Models\Credential;
use App\Repositories\CredentialRepository;
use App\Services\Platforms\Platform;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\ValidationException;

class CredentialStrategy implements iCredentialStrategy
{
    use Macroable;

    /**
     * @var iConnector
     */
    protected iConnector $connector;

    public function __construct(iConnector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param StoreCredentialRequest $request
     * @return Credential
     * @throws ValidationException
     */
    public function createCredential(StoreCredentialRequest $request)
    {
        $credential = CredentialRepository::create($request);
        return $this->validate($request, $credential);
    }

    /**
     * @param UpdateCredentialRequest $request
     * @param Credential $credential
     * @return String
     * @throws ValidationException
     */
    public function updateCredential(UpdateCredentialRequest $request, Credential $credential)
    {
        $credential = CredentialRepository::update($credential, $request);

        return $this->validate($request, $credential,
            function () use ($credential, $request) {
                $changes = $credential->getChanges();

                if (array_key_exists('synchronized', $changes) && !$changes['synchronized']) {
                    event(new CredentialToDesynchronize($credential));
                }

                if ($credential->platform->has_agendas) {
                    $platform = Platform::resolve($credential);
                    $platform->setUser($credential);

                    if (array_key_exists($request->agenda_id, $platform->getAgendas())) {
                        $credential->forceFill([
                            'agenda' => $request->agenda_id,
                            'valid' => true
                        ])->save();
                    }
                    else {
                        throw CredentialException::wrongAgenda();
                    }
                }
            });
    }

    /**
     * @param Credential $credential
     * @throws \Exception
     */
    public function deleteCredential(Credential $credential)
    {
        $credential->forceDelete();
    }

    protected function validate($request, Credential $credential, $closure = null)
    {
        if ($this->connector->isValidConnectionRequest($request->secret) === true) {
            return $this->connector->attemptConnection($credential, $closure);
        }
        throw CredentialException::badRequest();
    }
}
