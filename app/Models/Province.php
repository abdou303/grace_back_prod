<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    //
    use HasFactory;

    protected $fillable = ['libelle', 'active'];
	

    public function garants()
    {
        return $this->hasMany(Garant::class);
    }
}
