<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutRequette extends Model
{
    use HasFactory;

    //
    public function requettes()
    {

        return $this->belongsToMany(Requette::class, 'requette_statut_requette')->withTimestamps();
    }
}
