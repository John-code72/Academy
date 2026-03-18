<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleRating extends Model
{
    protected $fillable = ['user_id', 'bundle_id', 'rating', 'comment'];
}
