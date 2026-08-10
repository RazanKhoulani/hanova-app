<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = null;
        if (method_exists($this->resource, 'getRoleNames')) {
            $role = $this->getRoleNames()->first();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $role,
            'roles' => method_exists($this->resource, 'getRoleNames') ? $this->getRoleNames() : [],
            'phone_verified_at' => $this->phone_verified_at,
            'created_at' => $this->created_at,
        ];
    }
}
