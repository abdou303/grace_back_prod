<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffaireResource extends JsonResource
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
            'numeromp' => $this->numeromp,
            'numero' => $this->numero,
            'code' => $this->code,
            'annee' => $this->annee,
            'datejujement' => $this->datejujement,
            'conenujugement' => $this->conenujugement,
            'nbrannees' => $this->nbrannees,
            'nbrmois' => $this->nbrmois,
            'peine_id' => $this->peine_id,
            'tribunal_id' => $this->tribunal_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'pivot' => [
                'dossier_id' => $this->pivot->dossier_id,
                'affaire_id' => $this->pivot->affaire_id,
                'created_at' => $this->pivot->created_at,
                'updated_at' => $this->pivot->updated_at,
            ],
        ];
    }
}
