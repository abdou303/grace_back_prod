<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;
	

    public function detenus()
    {

        return $this->hasMany(Detenu::class);
    }
}
