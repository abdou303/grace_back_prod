<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garant extends Model
{
    use HasFactory;
    public function dossiers()
    {

        return $this->belongsToMany(Dossier::class)->withTimestamps();
    }
}
