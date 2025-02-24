<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pj extends Model
{

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
