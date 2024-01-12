<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetVendorProfile extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vendor = [
            'id' => isset($this->id)? $this->id : null,
            'name' => isset($this->name)? $this->name : null,
            'email' => isset($this->email)? $this->email : null,
            'firstName' => isset($this->vendor->first_name)? $this->vendor->first_name : null,
            'lastName' => isset($this->vendor->last_name)? $this->vendor->last_name : null,
            'phoneNumber' => isset($this->vendor->phone_number)? $this->vendor->phone_number : null,
            'companyName' => isset($this->vendor->company_name)? $this->vendor->company_name : null,
            'companyEmail' => isset($this->vendor->company_email)? $this->vendor->company_email : null,
            'companyWebsite' => isset($this->vendor->company_website)? $this->vendor->company_website : null,
            'companyAddress' => isset($this->vendor->company_address)? $this->vendor->company_address : null,
            'logo' => isset($this->vendor->logo)? $this->vendor->logo : null,
            'planName' => $this->vendor->plan->name,
            'pin' => isset($this->vendor->pin)? $this->vendor->pin : null, 
            'vendorId' => isset($this->vendor->id)? $this->vendor->id : null,
        ];
        return $vendor;
    }
}
