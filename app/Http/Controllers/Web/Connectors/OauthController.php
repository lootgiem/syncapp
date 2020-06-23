<?php

namespace App\Http\Controllers\Web\Connectors;

use App\Http\Requests\OauthCodeRequest;
use App\Models\Credential;
use App\Services\Platforms\Platform;
use Illuminate\Http\RedirectResponse;

class OauthController
{
    /**
     * Redirect the new client to the auth page related to the credential flow.
     *
     * @param Credential $credential
     * @return RedirectResponse
     */
    public function redirectToAuth(Credential $credential)
    {
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
