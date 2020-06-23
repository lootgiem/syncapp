<?php


namespace App\Repositories;


use App\Models\Credential;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CredentialRepository
{
    /**
     * @param $userId
     * @return Credential[]|Collection
     */
    public static function forUser($userId)
    {
        return Credential::withoutTrashed()
            ->where('user_id', $userId)
            ->orderBy('name', 'asc')->get();
    }

    /**
     * @param $userId
     * @return Credential[]|Collection
     */
    public static function toSynchronizeForUser($userId)
    {
        return Credential::withoutTrashed()
            ->where('user_id', $userId)
            ->Where('valid', '=', true)
            ->Where('synchronized', '=', true)->get();
    }

    public static function create($request, $valid = false)
    {
        $credential = new Credential();
        $credential = $credential->fill($request->all());

        return $credential->forceFill([
            'platform_id' => $request->platform_id,
            'user_id' => $request->user()->id,
            'secret' => $request->secret,
            'valid' => $valid
        ]);
    }

    public static function update(Credential $credential, $request, $valid = false)
    {
        $credential = $credential->fill($request->all());


        return $credential->forceFill([
            'secret' => static::mergeSecret($credential->secret, $request->secret),
            'valid' => $valid
        ]);
    }

    private static function mergeSecret(array $credential_secret, array $request_secret)
    {
        if (!empty($request_secret)) {
            foreach ($request_secret as $key => $value) {
                $request_secret[$key] = empty($value) && isset($credential_secret[$key]) ? $credential_secret[$key] : $value;
            }
            return $request_secret;
        }

        return $credential_secret;
    }

    public static function generateToken(Credential $credential)
    {
        $token = Str::random(30);
        $credential->forceFill([
            'token' => hash('sha256', date('Y-m-d') . $token),
        ])->save();

        return $token;
    }
}
