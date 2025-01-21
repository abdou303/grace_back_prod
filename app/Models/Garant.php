<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garant extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom', 'adresse','genre','qualite','province_id','tribunal_id'];
    public function dossiers()
    {

        return $this->belongsToMany(Dossier::class)->withTimestamps();
    }

    public function province()
    {

        return $this->belongsTo(Province::class, 'province_id');
    }

    public function tribunal()
    {

        return $this->belongsTo(Tribunal::class, 'tribunal_id');
    }
}
