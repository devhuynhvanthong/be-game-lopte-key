<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;
    protected $table = TABLE_SERVICES;
    protected $fillable = [FIELD_ID, FIELD_NAME, FIELD_CODE,FIELD_END_POINT];
    public $timestamps = false;
}
