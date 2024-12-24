<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieDossier extends Model
{
    use HasFactory;
    protected $table = 'categoriesdossiers';

    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
