<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\BundleRating;
use App\Models\CourseBundle;
use App\Models\BundlePayment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CourseBundleController extends Controller
{
    public function index()
    {
        $query = CourseBundle::join('users', 'course_bundles.user_id', 'users.id')
            ->select(
                'course_bundles.*',
                'users.name as instructor_name',
                'users.email as instructor_email',
                'users.photo as instructor_image'
            )
            ->where('course_bundles.status', 'active');

        if (request()->has('search')) {
            $query->where('course_bundles.title', 'LIKE', '%' . request()->query('search') . '%');
        }

        $page_data['course_bundles'] = $query->paginate(9)->appends(request()->query());

        return view(theme_path() . 'course_bundle.index', $page_data);
    }

    public function show($slug)
    {
        // Fetch bundle details by slug
        $page_data['bundle'] = CourseBundle::where('slug', $slug)->firstOrFail();
        $page_data['ratings'] = BundleRating::where('bundle_id', $page_data['bundle']->id)->get();
        $page_data['total_rating'] = $page_data['ratings']->sum('rating');

        $page_data['average_rating'] = $page_data['ratings']->count() > 0
            ? ceil($page_data['total_rating'] / $page_data['ratings']->count())
            : 0;

        $course_ids = json_decode($page_data['bundle']->course_ids);
        $page_data['courses'] = Course::whereIn('id', $course_ids)->where('status', 'active')->get();

        return view(theme_path() . 'course_bundle.details', $page_data);
    }


}
