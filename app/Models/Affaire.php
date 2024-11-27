<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affaire extends Model
{
    use HasFactory;

    public function dossiers()
    {

        return $this->belongsToMany(Dossier::class)->withTimestamps();
    }

    public function tribunal()
    {

        return $this->belongsTo(Tribunal::class)->withTimestamps();
    }

    public function peine()
    {

        return $this->belongsTo(Peine::class)->withTimestamps();
    }
}
