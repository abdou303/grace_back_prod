<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotifGreffe extends Model
{
    protected $table = 'motifs_greffe';
    protected $fillable = ['libelle', 'code'];
}
