<?php

namespace Ingenius\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Ingenius\Core\Interfaces\HasCustomerProfile;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // If user implements HasCustomerProfile, include profile data
        if ($this->resource instanceof HasCustomerProfile) {
            $data['profile'] = [
                'firstname' => $this->resource->getFirstName(),
                'lastname' => $this->resource->getLastName(),
                'phone' => $this->resource->getPhone(),
                'address' => $this->resource->getAddress(),
                'full_name' => $this->resource->getFullName(),
                'is_complete' => $this->resource->hasCompleteProfile(),
            ];
        }

        return $data;
    }
}
