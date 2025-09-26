<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}
