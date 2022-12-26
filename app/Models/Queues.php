<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queues extends Model
{
    use HasFactory;
    protected $table = TABLE_QUEUES;
    protected $fillable = [FIELD_ID, FIELD_IP, FIELD_ID_KEY,FIELD_TIME_CREATE];
    public $timestamps = false;

    public function key(){
        return $this->belongsTo(Keys::class,FIELD_ID_KEY,FIELD_ID);
    }
}
