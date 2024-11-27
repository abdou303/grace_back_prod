<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaysResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [

            'id' => $this->id,
            'libelle' => $this->libelle,
            'libelle_fr' => $this->libelle_fr,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
