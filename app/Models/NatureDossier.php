<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NatureDossier extends Model
{
    use HasFactory;
    protected $table = 'naturesdossiers';
	
    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
