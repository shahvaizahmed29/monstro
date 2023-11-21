<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckInsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $check_ins = [
            'id' => $this->id,
            'reservationId' => $this->reservation_id,
            'checkInTime' => $this->check_in_time,
            'checkOutTime' => $this->check_out_time,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];

        return $check_ins;
    }
}
