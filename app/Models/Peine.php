<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peine extends Model
{
    use HasFactory;

    public function affaire()
    {

        return $this->hasOne(Affaire::class);
    }

    public function prisons()
    {

        return $this->belongsToMany(Prison::class)->withTimestamps();
    }
}
