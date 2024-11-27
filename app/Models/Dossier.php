<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    use HasFactory;

    public function affaires()
    {

        return $this->belongsToMany(Affaire::class)->withTimestamps();
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

        return $this->belongsToMany(Garant::class)->withTimestamps();
    }

    public function requettes()
    {

        return $this->hasMany(Requette::class);
    }
}
