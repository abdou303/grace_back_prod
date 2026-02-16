<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilisateurResource extends JsonResource
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
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,

            // On récupère le libellé via la relation
            'role' => $this->role?->libelle,
            'groupe' => $this->groupe?->libelle,
            'tribunal' => $this->tribunal?->libelle,
            'created_at' => $this->created_at,
        ];
    }
}
