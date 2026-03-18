<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BundlePayment;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\CourseBundle;
use App\Models\FileUploader;
use App\Models\SeoField;
use App\Models\Payment_history;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CourseBundleController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Dhaka');
    }

    // public function index(Request $request, $type = '')
    // {
    //     // $query = CourseBundle::where('user_id', auth()->user()->id);
    //     $query = CourseBundle::query();
    //     if (request()->has('search') && request()->query('search') != '') {
    //         $query = $query->where('title', 'like', '%' . request()->query('search') . '%');
    //     }
    //     $status = 'all';

    //     // status filter
    //     if (isset($_GET['status']) && $_GET['status'] != '' && $_GET['status'] != 'all') {

    //         if ($_GET['status'] == 'active') {
    //             $search_status = 'active';
    //             $query = $query->where('status', $search_status);
    //         } elseif ($_GET['status'] == 'inactive') {
    //             $search_status = 'inactive';
    //             $query = $query->where('status', $search_status);
    //         }
    //         $status = $_GET['status'];
    //     }
    //     $page_data['status'] = $status;

    //     // Status Filter
    //     if ($request->has('status') && $request->status != 'all') {
    //         $query->where('status', $request->status);
    //     }

    //     $page_data['course_bundles'] = $query->paginate(10)->appends(request()->query());
    //     return view('admin.course_bundle.index', $page_data);
    // }

        public function index(Request $request, $type = '')
    {
        $query = CourseBundle::query();

        // Search by bundle title OR user name
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $status = 'all';

        // Status filter (cleaned)
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
            $status = $request->status;
        }
        $page_data['status'] = $status;
        // Optional: eager load user (performance)
        $page_data['course_bundles'] = $query->with('user')->paginate(10)->appends($request->query());

        return view('admin.course_bundle.index', $page_data);
    }


    public function create()
    {
        return view('admin.course_bundle.create');
    }
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string',
            'course_ids' => 'required',
            'subscription_limit' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'bundle_details' => 'nullable|string',
        ];
        $title = CourseBundle::where('user_id', auth()->user()->id)->where('title', $request->title)->first();
        if ($title) {
            Session::flash('error', get_phrase('This title has been taken.'));
            return redirect()->back();
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $course_bundle['title'] = $request->title;
        $course_bundle['slug'] = slugify($request->title);
        $course_bundle['user_id'] = auth()->user()->id;
        $course_bundle['course_ids'] = json_encode($request->course_ids);
        $course_bundle['subscription_limit'] = $request->subscription_limit;
        $course_bundle['price'] = $request->price;
        $course_bundle['bundle_details'] = $request->bundle_details;
        $course_bundle['status'] = 'active';


        if ($request->thumbnail) {
            $course_bundle['thumbnail'] = "uploads/courseBundle/thumbnail/" . nice_file_name($request->title, $request->thumbnail->extension());
            FileUploader::upload($request->thumbnail, $course_bundle['thumbnail']);
        }
        CourseBundle::create($course_bundle);

        Session::flash('success', get_phrase('Course bundle has been created successfully.'));
        return redirect()->route('admin.course.bundles');
    }

    public function edit($id)
    {

        $page_data['course_bundle'] = CourseBundle::findOrFail($id);
        return view('admin.course_bundle.edit', $page_data);
    }

    public function update(Request $request, $id)
    {
        $query = CourseBundle::where('id', $id);

        if (!$query->exists()) {
            return redirect()->back()->with('error', get_phrase('Data not found.'));
        }

        $rules = [
            'title' => 'required|string|max:255',
            'course_ids' => 'required|min:1',
            'subscription_limit' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'bundle_details' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['title'] = $request->title;
        $data['slug'] = slugify($request->title);

        $data['course_ids'] = json_encode($request->course_ids);
        $data['subscription_limit'] = $request->subscription_limit;
        $data['price'] = $request->price;
        $data['bundle_details'] = $request->bundle_details;

        $title = CourseBundle::where('user_id', auth()->user()->id)
            ->where('id', '!=', $id)
            ->where('title', $request->title)->first();
        if ($title) {
            Session::flash('error', get_phrase('This title has been taken.'));
            return redirect()->back();
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = "uploads/course-bundle-banner/" . nice_file_name($request->title, $request->banner->extension());
            FileUploader::upload($request->banner, $data['banner'], 1400, null, 300, 300);
            remove_file($query->first()->banner);

        }

        if ($request->has('meta_title') || $request->has('meta_description')) {
            $course_bundle = $query->first();
            $SeoField = SeoField::where('name_route', 'course.bundle.details')->where('bundle_id', $course_bundle->id)->first();

            $seo_data['bundle_id'] = $id;
            $seo_data['route'] = 'Bundle Details';
            $seo_data['name_route'] = 'course.bundle.details';
            $seo_data['meta_title'] = $request->meta_title;
            $seo_data['meta_description'] = $request->meta_description;
            $seo_data['meta_robot'] = $request->meta_robot;
            $seo_data['canonical_url'] = $request->canonical_url;
            $seo_data['custom_url'] = $request->custom_url;
            $seo_data['json_ld'] = $request->json_ld;
            $seo_data['og_title'] = $request->og_title;
            $seo_data['og_description'] = $request->og_description;
            $seo_data['created_at'] = date('Y-m-d H:i:s');
            $seo_data['updated_at'] = date('Y-m-d H:i:s');


            $meta_keywords_arr = json_decode($request->meta_keywords, true);
            $meta_keywords = '';
            if (is_array($meta_keywords_arr)) {
                foreach ($meta_keywords_arr as $arr_key => $arr_val) {
                    $meta_keywords .= $meta_keywords == '' ? $arr_val['value'] : ', ' . $arr_val['value'];
                }
                $seo_data['meta_keywords'] = $meta_keywords;
            }


            if ($request->og_image) {
                $originalFileName = $course_bundle->id . '-' . $request->og_image->getClientOriginalName();
                $destinationPath = 'uploads/seo-og-images/' . $originalFileName;
                // Move the file to the destination path
                FileUploader::upload($request->og_image, $destinationPath, 600);
                $seo_data['og_image'] = $destinationPath;
            }

            if ($SeoField) {
                if ($request->og_image) {
                    remove_file($SeoField->og_image);
                }

                SeoField::where('name_route', 'course.bundle.details')->where('bundle_id', $course_bundle->id)->update($seo_data);
            } else {
                SeoField::insert($seo_data);
            }
        }

        $query->update($data);
        return redirect(route('admin.course.bundles'))->with('success', get_phrase('Course bundle updated successfully.'));
    }

    public function status($id, $type = 'active')
    {
        $status = ($type === 'active') ? 'active' : 'inactive';

        CourseBundle::where('id', $id)->update(['status' => $status]);

        return redirect(route('admin.course.bundles'))->with('success', get_phrase('Course bundle status changed successfully.'));
    }


    public function delete($id)
    {
        $bundle = CourseBundle::findOrFail($id);
        remove_file($bundle->banner);
        $bundle->delete();

        return redirect(route('admin.course.bundles'))->with('success', get_phrase('Course bundle deleted successfully.'));
    }

    public function currentPrice(Request $request)
    {
        $courseIds = $request->selected_course_id;
        $totalPrice = Course::whereIn('id', $courseIds)->sum('price');

        return response()->json($totalPrice);
    }

    public function subscriptionReport(Request $request)
    {
        $query = BundlePayment::query();

        if (request()->has('search') && request()->query('search') != '') {
            $query = $query->whereHas('courseBundle', function ($q1) {
                $q1->where('title', 'like', '%' . request()->query('search') . '%');
            });
        }

        $status = 'all';

        // Filter by status
        if ($request->has('status') && $request->input('status') != 'all') {
            $status = $request->input('status');
            $currentTime = now();

            $query->whereHas('bundle', function ($q) use ($status, $currentTime) {
                if ($status == 'active') {
                    $q->whereRaw('DATE_ADD(created_at, INTERVAL subscription_limit DAY) > ?', [$currentTime]);
                } else {
                    $q->whereRaw('DATE_ADD(created_at, INTERVAL subscription_limit DAY) <= ?', [$currentTime]);
                }
            });
        }
        $page_data['bundle_histories'] = $query->paginate(10);


        return view('admin.course_bundle.subscription_report', $page_data);
    }


   
     public function adminRevenue(Request $request)
    {
        if ($request->eDateRange) {
            $date                    = explode('-', $request->eDateRange);
            $start_date              = strtotime($date[0] . ' 00:00:00');
            $end_date                = strtotime($date[1] . ' 23:59:59');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;

            $page_data['reports'] = BundlePayment::whereNotNull('bundle_id')->where('admin_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10)->appends($request->query());
        } else {
            $start_date              = strtotime('first day of this month');
            $end_date                = strtotime('last day of this month');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;
            $page_data['reports']    = BundlePayment::whereNotNull('bundle_id')->where('admin_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))->latest('id')->paginate(10);
        }

        return view('admin.course_bundle.admin_revenue', $page_data);
    }

     public function instructorRevenue(Request $request)
    {
        if ($request->eDateRange) {
            $date                            = explode('-', $request->eDateRange);
            $start_date                      = strtotime($date[0] . ' 00:00:00');
            $end_date                        = strtotime($date[1] . ' 23:59:59');
            $page_data['start_date']         = $start_date;
            $page_data['end_date']           = $end_date;
            $page_data['reports'] = BundlePayment::whereNotNull('bundle_id')->where('instructor_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10)->appends($request->query());
        } else {
            $start_date              = strtotime('first day of this month');
            $end_date                = strtotime('last day of this month');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;
            $page_data['reports']    = BundlePayment::whereNotNull('bundle_id')->where('instructor_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10);
        }
        return view('admin.course_bundle.instructor_revenue', $page_data);
    }
        
        


}
