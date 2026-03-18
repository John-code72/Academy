<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeBoard extends Model
{
    protected $table = 'noticeboard';
   protected $fillable = [
       'course_id',
        'title',
        'description',
        
    ];
}
