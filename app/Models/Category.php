<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = TABLE_CATEGORY;
    public $timestamps = false;
    protected $fillable = [FIELD_ID,FIELD_CODE, FIELD_NAME];

}
