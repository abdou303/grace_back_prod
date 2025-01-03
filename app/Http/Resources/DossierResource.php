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
        /**/
        return parent::toArray($request);
        /* return [
            'id' => $this->id,
            'numero' => $this->numero,
            'date_enregistrement' => $this->date_enregistrement,
            'observation' => $this->observation,
            'avis_mp' => $this->avis_mp,
            'avis_dgapr' => $this->avis_dgapr,
            'avis_gouverneur' => $this->avis_gouverneur,
            'typedossier_id' => $this->typedossier_id,
            'detenu_id' => $this->detenu_id,
            'genre' => $this->genre,
            'categoriedossiers_id' => $this->categoriedossiers_id,
            'naturedossiers_id' => $this->naturedossiers_id,
            'typemotifdossiers_id' => $this->typemotifdossiers_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'detenu' => new DetenuResource($this->detenu),
            'affaires' => AffaireResource::collection($this->affaires),
            'categoriedossier' => new CategorieDossierResource($this->categoriedossier),
            'naturedossier' => new NatureDossierResource($this->naturedossier),
            'typemotifdossier' => new TypeMotifDossierResource($this->typemotifdossier),
            'typedossier' => new TypeDossierResource($this->typedossier),
        ];*/
    }
}
