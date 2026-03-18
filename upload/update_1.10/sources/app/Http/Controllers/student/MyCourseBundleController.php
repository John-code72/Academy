<?php

namespace App\Http\Controllers\student;
use App\Http\Controllers\Controller;
use App\Models\BundlePayment;
use App\Models\Course;
use App\Models\CourseBundle;
use App\Models\BundleRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class MyCourseBundleController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Dhaka');
    }
    public function index()
    {
        $page_data['my_course_bundles'] = BundlePayment::join('course_bundles', 'bundle_payments.bundle_id', 'course_bundles.id')
            ->where('bundle_payments.user_id', auth()->user()->id)
            ->where('bundle_payments.status', 1)
            ->where('course_bundles.status', 'active')
            ->select('course_bundles.*')->latest('id')->paginate(10)->appends(request()->query());
        return view(theme_path() . 'student.my_course_bundles.index', $page_data);
    }
    public function show($slug)
    {
        $page_data['course_bundle'] = BundlePayment::join('course_bundles', 'bundle_payments.bundle_id', 'course_bundles.id')
            ->where('bundle_payments.user_id', auth()->user()->id)
            ->where('bundle_payments.status', 1)
            ->where('course_bundles.slug', $slug)
            ->select('course_bundles.*')->latest('id')->first();

        if (!$page_data['course_bundle']) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }
        return view(theme_path() . 'student.my_course_bundles.details', $page_data);
    }


    public function store(Request $request, $id)
    {
        $data['bundle_id'] = $request->bundle_id;
        $data['user_id'] = auth()->user()->id;
        $data['rating'] = $request->rating;
        $data['comment'] = $request->comment;

        BundleRating::insert($data);

        Session::flash('success', 'Rating has been submitted successfully!');
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        if (!is_numeric($id) && $id < 1) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }
        $data['bundle_id'] = $request->bundle_id;
        $data['user_id'] = auth()->user()->id;
        $data['rating'] = $request->rating;
        $data['comment'] = $request->comment;

        BundleRating::where('id', $id)->update($data);

        Session::flash('success', 'Rating has been updated successfully!');
        return redirect()->back();

    }
    public function purchaseHistory($id)
    {
        $page_data['bundle'] = CourseBundle::find($id);
        $user_id = auth()->user()->id;

        $page_data['purchase_histories'] = BundlePayment::where('user_id', $user_id)->where('bundle_id', $id)->get();

        return view(theme_path() . 'student.my_course_bundles.purchase_history', $page_data);
    }

    public function invoice($id)
    {
        $invoice = BundlePayment::join('course_bundles', 'bundle_payments.bundle_id', 'course_bundles.id')
            ->where('bundle_payments.bundle_id', $id)->where('bundle_payments.user_id', auth()->user()->id)
            ->select(
                'bundle_payments.*',
                'course_bundles.title',
                'course_bundles.slug',
            )->first();

        if (!$invoice) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }

        $page_data['invoice'] = $invoice;
        $page_data['bundle'] = CourseBundle::find($id);

        return view(theme_path() . 'student.my_course_bundles.invoice', $page_data);
    }

}
