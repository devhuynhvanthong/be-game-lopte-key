<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $table = TABLE_SESSION;
    public $timestamps = false;
    protected $fillable = [FIELD_ID,FIELD_ID_ACCOUNT, FIELD_TOKEN];
}
