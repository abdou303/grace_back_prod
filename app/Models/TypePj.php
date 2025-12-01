<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePj extends Model
{
    use HasFactory;
    protected $table = 'typespjs';
	


    public function pjs()
    {

        return $this->hasMany(Pj::class);
    }
}
