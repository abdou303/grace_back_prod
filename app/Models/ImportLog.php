<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    //
    protected $fillable = [
        'nomdufichier',
        'date',
        'statut',
        'nb_lignes_total',
        'nb_lignes_importees',
        'nb_lignes_ignorees',
        'message_erreur',
        'user_id',
        'tribunal_id',
    ];
    protected $casts = ['date' => 'datetime'];
}
