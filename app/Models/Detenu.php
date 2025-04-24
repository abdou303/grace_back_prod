<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detenu extends Model
{
    use HasFactory;
    protected $fillable = [

        'nom',
        'prenom',
        'nompere',
        'nommere',
        'cin',
        'datenaissance',
        'genre'

    ];

    public function ville()
    {

        return $this->belongsTo(Ville::class);
    }

    public function profession()
    {

        return $this->belongsTo(Profession::class);
    }

    public function nationalite()
    {

        return $this->belongsTo(Nationalite::class);
    }

    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
