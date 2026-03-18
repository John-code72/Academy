<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'user_id',
        'course_ids',
        'subscription_limit',
        'thumbnail',
        'price',
        'bundle_details',
        'status',
        'banner',
    ];
    public function bundlePayments()
    {
        return $this->hasMany(BundlePayment::class, 'bundle_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
