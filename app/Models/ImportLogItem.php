<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLogItem extends Model
{
    protected $table = 'import_log_items';
    protected $fillable = ['import_type', 'import_log_id', 'model', 'model_id'];
}
