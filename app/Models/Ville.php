<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    use HasFactory;

    public function pays()
    {

        return $this->belongsTo(Pays::class);
    }

    public function detenus()
    {

        return $this->belongsToMany(Detenu::class)->withTimestamps();
    }
}
