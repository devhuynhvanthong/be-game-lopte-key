<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keys extends Model
{
    use HasFactory;
    protected $table = TABLE_KEYS;
    public $timestamps = false;
    protected $fillable = [FIELD_ID,FIELD_CODE, FIELD_TIME_CREATE, FIELD_ID_CATEGORY];
    public function category(){
        return $this->belongsTo(Category::class,FIELD_ID_CATEGORY,FIELD_ID);
    }
}
