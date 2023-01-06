<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigVisit extends Model
{
    use HasFactory;
    protected $table = TABLE_CONFIG_VISITS;
    public $timestamps = false;
    protected $fillable = [FIELD_ID,FIELD_ID_CATEGORY];
    public function category_key(){
        return $this->belongsTo(Category::class,FIELD_ID_CATEGORY,FIELD_ID);
    }
}
