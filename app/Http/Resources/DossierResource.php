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
        /*
        return [
            'id' => $request->id,
            'numero' => $request->numero,
            'date_enregistrement' => $request->date_enregistrement,
            'observation' => $request->observation,
            'avis_mp' => $request->avis_mp,
            'avis_dgapr' => $request->avis_dgapr,
            'avis_gouverneur' => $request->avis_gouverneur,
            'typedossier_id' => $request->typedossier_id,
            'detenu_id' => $request->detenu_id,
            'genre' => $request->genre,
            'categoriedossiers_id' => $request->categoriedossiers_id,
            'naturedossiers_id' => $request->naturedossiers_id,
            'typemotifdossiers_id' => $request->typemotifdossiers_id,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'detenu' => new DetenuResource($request->detenu),
            'affaires' => AffaireResource::collection($request->affaires),
            'categoriedossier' => new CategorieDossierResource($request->categoriedossier),
            'naturedossier' => new NatureDossierResource($request->naturedossier),
            'typemotifdossier' => new TypeMotifDossierResource($request->typemotifdossier),
            'typedossier' => new TypeDossierResource($request->typedossier),
        ];*/
    }
}
