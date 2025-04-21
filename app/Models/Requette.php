<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requette extends Model
{
    use HasFactory;
    protected $fillable = [


        'date',
        'numero',
        'contenu',
        'observations',
        'dossier_id',
        'user_id',
        'partenaire_id',
        'tribunal_id',
        'created_at',
        'updated_at',
        'typerequette_id',
        'date_importation',
        'etat'
    ];
    public function pjs()
    {

        return $this->hasMany(Pj::class);
    }

    public function partenaire()
    {

        return $this->belongsTo(Partenaire::class);
    }
    public function tribunal()
    {

        return $this->belongsTo(Tribunal::class);
    }

    public function dossier()
    {

        return $this->belongsTo(Dossier::class);
    }

    public function typerequette()
    {

        return $this->belongsTo(TypeRequette::class);
    }

    public function statutrequettes()
    {

        return $this->belongsToMany(StatutRequette::class, 'requette_statut_requette')->withTimestamps();
    }
}
