<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tribunal extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tribunaux';
	


    public function ca()
    {

        return $this->belongsTo(Ca::class);
    }
    public function affaires()
    {

        return $this->hasMany(Affaire::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function garants()
    {
        return $this->hasMany(Garant::class);
    }
}
