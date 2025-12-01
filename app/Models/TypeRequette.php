<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeRequette extends Model
{
    use HasFactory;
    protected $table = 'typesrequettes';

    
    public function requettes()
    {

        return $this->hasMany(Requette::class);
    }
}
