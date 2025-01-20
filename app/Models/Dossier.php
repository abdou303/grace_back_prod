<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    use HasFactory;
    protected $fillable = [
'numero',
'date_enregistrement',
'avis_mp',
'avis_dgapr',
'avis_gouverneur',
'typedossier_id',
'detenu_id',
'typemotifdossiers_id',
'categoriedossiers_id',
'naturedossiers_id'
    ];

    public function affaires()
    {

        return $this->belongsToMany(Affaire::class, 'dossier_affaire')->withTimestamps();
    }

    public function typedossier()
    {

        return $this->belongsTo(TypeDossier::class);
    }

    public function detenu()
    {

        return $this->belongsTo(Detenu::class);
    }
    public function garants()
    {

        return $this->belongsToMany(Garant::class, 'dossier_garant')->withTimestamps();
    }

    public function requettes()
    {

        return $this->hasMany(Requette::class);
    }
    public function categoriedossier()
    {

        return $this->belongsTo(CategorieDossier::class, 'categoriedossiers_id');
    }
    public function naturedossier()
    {

        return $this->belongsTo(NatureDossier::class, 'naturedossiers_id');
    }
    public function typemotifdossier()
    {

        return $this->belongsTo(TypeMotifDossier::class, 'typemotifdossiers_id');
    }

    public function comportement()
    {

        return $this->belongsTo(Comportement::class, 'comportement_id');
    }
}
