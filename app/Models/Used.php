<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Used extends Model
{
    use HasFactory;
    protected $table = TABLE_USED;
    protected $fillable = [FIELD_ID, FIELD_ID_KEY, FIELD_TIME,FIELD_IP];
    public $timestamps = false;
    public function keys(){
        return $this->belongsTo(Keys::class,FIELD_ID_KEY,FIELD_ID);
    }
}
