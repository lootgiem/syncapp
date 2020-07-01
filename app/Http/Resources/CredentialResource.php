<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CredentialResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'platform' => new PlatformResource($this->platform),
            'name' => $this->name,
            'agenda' => $this->agenda,
            'redirect' => $this->redirect,
            'synchronized' => $this->synchronized
        ];
    }
}
