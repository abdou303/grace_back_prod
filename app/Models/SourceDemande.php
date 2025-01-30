<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceDemande extends Model
{
    //

    use HasFactory;
    protected $table = 'sourcesdemandes';
    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
