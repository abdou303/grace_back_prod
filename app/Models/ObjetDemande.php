<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjetDemande extends Model
{
    use HasFactory;
    protected $table = 'objetsdemandes';
	

    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
