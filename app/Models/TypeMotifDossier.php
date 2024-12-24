<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeMotifDossier extends Model
{
    use HasFactory;
    protected $table = 'typesmotifsdossiers';
    public function dossiers()
    {

        return $this->hasMany(Dossier::class);
    }
}
