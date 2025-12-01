<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comportement extends Model
{
    use HasFactory;
    protected $table = 'comportements';
	

    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
