<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ca extends Model
{
    use HasFactory;
	
    public function tribunaux()
    {

        return $this->hasMany(Tribunal::class);
    }
}
