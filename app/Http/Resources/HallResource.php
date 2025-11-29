<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HallResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'owner_name'          => $this->owner_name,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'address'             => $this->address,
            'city'                => $this->city,
            'state'               => $this->state,
            'country'             => $this->country,
            'zipcode'             => $this->zipcode,
            'area'                => $this->area,
            'halls_count'         => $this->halls_count,
            'hall_types'          => $this->hall_types,
            'registration_number' => $this->registration_number,
            'established_at'      => $this->established_at,
            'status'              => $this->status,
            'notes'               => $this->notes,
            'users'               => $this->users,
        ];
    }
}
