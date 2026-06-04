<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    public $table       = 'custom_fields';
    protected $fillable = [
        'course_id',
        'custom_type',
        'custom_title',
        'custom_field',
        'sorting',
    ];
}
