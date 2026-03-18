<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_history extends Model
{
    use HasFactory;

     public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function bundle()
    {
        return $this->belongsTo(CourseBundle::class, 'bundle_id');
    }
    
}


