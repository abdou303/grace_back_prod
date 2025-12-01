<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'numeromp',
        'numeroaffaire',
        'numero',
        'code',
        'annee',
        'datejujement',
        'conenujugement',
        'nbrannees',
        'nbrmois',
        'peine_id',
        'tribunal_id',
    ];
	

    public function dossiers()
    {

        return $this->belongsToMany(Dossier::class, 'dossier_affaire')->withTimestamps();
    }
    public function pjs()
    {

        return $this->hasMany(Pj::class);
    }
    /* public function tribunal()
    {

        return $this->belongsTo(Tribunal::class,'tribunal_id')->withTimestamps();
    }*/
    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'tribunal_id');
    }
    public function peine()
    {

        return $this->belongsTo(Peine::class, 'peine_id');
    }
}
