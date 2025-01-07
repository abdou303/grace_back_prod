<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requette extends Model
{
    use HasFactory;
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
}
