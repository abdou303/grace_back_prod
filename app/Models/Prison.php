<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prison extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'libelle'];

    public function peines()
    {

        return $this->belongsToMany(Peine::class, 'peine_prison')->withTimestamps();
    }

    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
