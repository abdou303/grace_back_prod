<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pj extends Model
{

    protected $fillable = [
        'contenu'
      ,'observation'
      ,'typepj_id'
      ,'requette_id'
      ,'affaire_id'
      ,'dossier_id'
      ,'openbee_url'
   

    ];
	public function dossier()
    {

        return $this->belongsTo(Dossier::class);
    }

    public function requette()
    {

        return $this->belongsTo(Requette::class);
    }

    public function affaire()
    {

        return $this->belongsTo(Affaire::class);
    }

    public function typepj()
    {

        return $this->belongsTo(TypePj::class);
    }
}
