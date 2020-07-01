<?php

namespace App\Http\Controllers\Web\Connectors;

use App\Http\Controllers\Controller;
use App\Http\Requests\OauthCodeRequest;
use App\Models\Credential;
use App\Services\Platforms\Platform;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;

class OauthController extends Controller
{
    /**
     * Redirect the new client to the auth page related to the credential flow.
     *
     * @param Credential $credential
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function redirectToAuth(Credential $credential)
    {
        $this->authorize('update', $credential);
        $platform = Platform::resolve($credential);
        $redirectUrl = $platform->getCredentialStrategy()->getAuthUrl($credential);
        return redirect()->away($redirectUrl);
    }

    /**
     * Validate the Oauth connection for the given credential
     *
     * @param OauthCodeRequest $request
     * @return null
     */
    public function callback(OauthCodeRequest $request)
    {
        $platform = Platform::resolve($request->credential);
        $platform->getCredentialStrategy()->generateAccessTokenFromCode($request->credential, $request->code);
        return redirect()->route('profil');
    }
}
