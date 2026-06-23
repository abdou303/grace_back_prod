<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportEncoursLog extends Model
{
    //
    protected $table = 'import_encours_logs';

    protected $fillable = [
        'nomdufichier',
        'date',
        'statut',
        'user_id',
        'tribunal_id',
        'nb_lignes_total',
        'nb_lignes_importees',
        'nb_lignes_ignorees',
        'message_erreur',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}
