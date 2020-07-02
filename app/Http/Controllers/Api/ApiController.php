<?php

namespace App\Http\Controllers\Api;

use App\Events\CredentialToDesynchronize;
use App\Http\Controllers\Controller;
use App\Http\Resources\CredentialResource;
use App\Http\Resources\PlatformResource;
use App\Jobs\SynchronizeJob;
use App\Models\Credential;
use App\Repositories\CredentialRepository;
use App\Repositories\PlatformRepository;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function platforms()
    {
        return PlatformResource::collection(PlatformRepository::getAvailable());
    }

    public function credentials(Request $request)
    {
        $credentials = CredentialRepository::forUser($request->user()->id);
        return CredentialResource::collection($credentials);
    }

    public function desynchronize(Credential $credential)
    {
        $this->authorize('update', $credential);
        event(new CredentialToDesynchronize($credential));
        return response()->json(['message' => 'Desynchronize job added to the queue']);
    }

    public function synchronize(Request $request)
    {
        $userId = $request->user()->id;
        SynchronizeJob::dispatch($userId);
        return response()->json(['message' => 'Synchronize job added to the queue']);
    }
}
