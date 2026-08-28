<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occasion extends Model
{
    protected $fillable = ['libelle', 'id_dapg'];

    public function dossiers()
    {
        return $this->belongsToMany(Dossier::class, 'dossier_occasion');
    }
}
