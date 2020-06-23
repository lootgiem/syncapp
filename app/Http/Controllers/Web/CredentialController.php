<?php

namespace App\Http\Controllers\Web;

use App\Events\CredentialDeleted;
use App\Events\CredentialToDesynchronize;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCredentialRequest;
use App\Http\Requests\UpdateCredentialRequest;
use App\Http\Resources\CredentialResource;
use App\Models\Credential;
use App\Repositories\CredentialRepository;
use App\Services\Platforms\Platform;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class CredentialController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Credential::class, 'credential');
    }

    /**
     * Return a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $platformCredentials = CredentialRepository::forUser($request->user()->id);
        return CredentialResource::collection($platformCredentials);
    }

    /**
     * Store a newly created credential.
     *
     * @param StoreCredentialRequest $request
     * @return CredentialResource
     * @throws ValidationException
     */
    public function store(StoreCredentialRequest $request)
    {
        $platform = Platform::resolve($request);
        $credential = $platform->getCredentialStrategy()->createCredential($request);
        return new CredentialResource($credential);
    }

    /**
     * Return the single credential.
     *
     * @param Credential $credential
     * @return CredentialResource
     */
    public function show(Credential $credential)
    {
        return new CredentialResource($credential);
    }

    /**
     * Update the specified credential in storage.
     *
     * @param UpdateCredentialRequest $request
     * @param Credential $credential
     * @return CredentialResource
     * @throws ValidationException
     */
    public function update(UpdateCredentialRequest $request, Credential $credential)
    {
        $platform = Platform::resolve($request);
        $credential = $platform->getCredentialStrategy()->updateCredential($request, $credential);
        return new CredentialResource($credential);
    }

    /**
     * Remove the specified platform credential from storage.
     *
     * @param Credential $credential
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Credential $credential)
    {
        $credential->delete();

        event(new CredentialToDesynchronize($credential, function () use ($credential) {
            $platform = Platform::resolve($credential);
            $platform->getCredentialStrategy()->deleteCredential($credential);
        }));

        return response()->json(['message' => 'ok']);
    }
}
