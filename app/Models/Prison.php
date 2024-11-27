<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prison extends Model
{
    use HasFactory;

    public function peines()
    {

        return $this->belongsToMany(Peine::class)->withTimestamps();
    }
}
