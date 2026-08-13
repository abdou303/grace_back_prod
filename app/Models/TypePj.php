<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePj extends Model
{
    use HasFactory;
    protected $table = 'typespjs';

    protected $fillable = ['libelle', 'active', 'active_dapg', 'active_tr'];

    protected $casts = [
        'active' => 'boolean',
        'active_dapg' => 'boolean',
        'active_tr' => 'boolean',
    ];

    public function pjs()
    {

        return $this->hasMany(Pj::class);
    }
}
