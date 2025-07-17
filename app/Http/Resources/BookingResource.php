<?php

namespace App\Http\Resources;

use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id'            => $this->id,
            'customer_name' => $this->customer->full_name,
            'service_name'  => $this->service->name,
            'starts_at'     => $this->starts_at->format("Y-m-d"),
            'ends_at'       => $this->ends_at->format("Y-m-d"),
            'status'        => $this->status,
            'total_price'   => $this->when(BookingStatus::CONFIRMED == $this->status, $this->dollar_format)
        ];
    }
}
