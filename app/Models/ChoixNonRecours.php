<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChoixNonRecours extends Model
{
    protected $table = 'choix_non_recours'; // sinon Laravel cherche "choix_non_recourses"
    protected $fillable = ['libelle', 'active'];
}
