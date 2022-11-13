<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encryption extends Model
{
    use HasFactory;
    protected $table = TABLE_ENCRYPTION;
    protected $fillable = [FIELD_ID, FIELD_CODE, FIELD_ID_ACCOUNT];
}
