<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DossierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
       /* return [
            'id' => $this->id,
            'numero' => $this->numero,
            'date_enregistrement' => $this->date_enregistrement,
            'observation' => $this->observation,
            'avis_mp' => $this->avis_mp,
            'avis_dgapr' => $this->avis_dgapr,
            'avis_gouverneur' => $this->avis_gouverneur,
            'detenu' => new DetenuResource($this->whenLoaded('detenu')),
            'affaires' => $this->whenLoaded('affaires', function () {
                return $this->affaires->map(function ($affaire) {
                    return [
                       
                        
                        'numeromp' => $affaire->numeromp,
                        'numero' => $affaire->numero,
                        'code' => $affaire->code,
                        'annee' => $affaire->annee,
                        'datejujement' => $affaire->datejujement,
                        'conenujugement' => $affaire->conenujugement,
                        'nbrannees' => $affaire->nbrannees,
                        'nbrmois' => $affaire->nbrmois,
                        'tribunal' => $affaire->tribunal ? $affaire->tribunal->libelle : null, // Include tribunal libelle
                    ];
                });
            }),
            'categoriedossier' => new CategoriedossierResource($this->whenLoaded('categoriedossier')),
            'naturedossier' => new NaturedossierResource($this->whenLoaded('naturedossier')),
            'typemotifdossier' => new TypemotifdossierResource($this->whenLoaded('typemotifdossier')),
            'typedossier' => new TypedossierResource($this->whenLoaded('typedossier')),
        ];*/
    }
}
