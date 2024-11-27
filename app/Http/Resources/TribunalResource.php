<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TribunalResource extends JsonResource
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
            'libelle_small' => $this->libelle_small,
            'type_tribunal' => $this->type_tribunal,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,


        ];
    }
}
