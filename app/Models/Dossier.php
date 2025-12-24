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
        'naturedossiers_id',
        'prison_id',
        'user_id',
        'numero_detention',
        'numero_dapg',
        'date_sortie',
        'tr_tribunal',
        'date_tr_tribunal',
        'tr_dapg',
        'date_tr_dapg',
        'has_antecedent',
        'antecedant_id',
        'date_etat_greffe',
        'date_envoi_greffe',
        'etat_greffe',
        'user_tribunal',
        'user_greffe',
        'user_parquet',
        'nbr_redirection'

    ];

    public function affaires()
    {

        return $this->belongsToMany(Affaire::class, 'dossier_affaire')->withTimestamps();
    }

    public function typedossier()
    {

        return $this->belongsTo(TypeDossier::class);
    }


    public function user()
    {

        return $this->belongsTo(User::class);
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

    public function objetdemande()
    {

        return $this->belongsTo(ObjetDemande::class, 'objetdemande_id');
    }


    public function sourcedemande()
    {

        return $this->belongsTo(SourceDemande::class, 'sourcedemande_id');
    }
    public function prison()
    {

        return $this->belongsTo(Prison::class, 'prison_id');
    }

    public function comportement()
    {

        return $this->belongsTo(Comportement::class, 'comportement_id');
    }
    public function pjs()
    {

        return $this->hasMany(Pj::class);
    }
}
