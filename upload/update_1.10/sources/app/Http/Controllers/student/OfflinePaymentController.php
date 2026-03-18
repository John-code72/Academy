<?php

namespace App\Http\Controllers\student;

use App\Mail\CourseBundleMail;
use App\Models\CourseBundle;
use App\Models\BundlePayment;
use App\Models\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\FileUploader;
use App\Models\OfflinePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OfflinePaymentController extends Controller
{
    public function store(Request $request)
    {

        // check amount
        $payment_details = Session::get('payment_details');
        
        $item_id_arr = [];
        foreach($payment_details['items'] as $item){
            $item_id_arr[] = $item['id'];
        }

        $rules = [
            'doc'      => 'required|mimes:jpeg,jpg,pdf,txt,png,docx,doc|max:3072',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    
        $file      = $request->doc;
        $file_name = Str::random(20) . '.' . $file->extension();
        $path      = 'uploads/offline_payment/' . slugify(auth()->user()->name) . '/' . $file_name;
        FileUploader::upload($file, $path, null, null, 300);

        $offline_payment['user_id']      = auth()->user()->id;
        $offline_payment['item_type']    = $request->item_type;
        $offline_payment['items']        = json_encode($item_id_arr);
        $offline_payment['tax']          = $payment_details['tax'];
        $offline_payment['total_amount'] = $payment_details['payable_amount'];
        $offline_payment['doc']          = $path;
        $offline_payment['coupon'] = $payment_details['coupon'] ?? null;

        // insert offline payment history
        OfflinePayment::insert($offline_payment);
        
        // remove from cart
        if ($request->item_type == 'course') {
            $url = 'purchase.history';
            CartItem::whereIn('course_id', $item_id_arr)->where('user_id', auth()->user()->id)->delete();
        } elseif ($request->item_type == 'bootcamp') {
            $url = 'bootcamps';
        } elseif ($request->item_type == 'package') {
            $url = 'team.packages';
        } elseif ($request->item_type == 'tutor_booking') {
            $url = 'tutor_list';
        }elseif ($request->item_type == 'ebook') {
            $url = 'ebooks';
        }elseif ($request->item_type == 'bundle') {
            $url = 'course.bundles';
        }

        // return to courses
        Session::flash('success', get_phrase('The payment will be completed once the admin reviews and approves it.'));
        return redirect()->route($url);
    }


//     public function store(Request $request)
// {
//     // Validate uploaded document
//     $rules = [
//         'doc' => 'required|mimes:jpeg,jpg,pdf,txt,png,docx,doc|max:3072',
//     ];
//     $validator = Validator::make($request->all(), $rules);
//     if ($validator->fails()) {
//         return redirect()->back()->withErrors($validator)->withInput();
//     }

//     // Get payment details from session
//     $payment_details = Session::get('payment_details');
//     if (!$payment_details) {
//         Session::flash('error', get_phrase('Payment session expired.'));
//         return redirect()->back();
//     }

//     $item_id_arr = [];
//     foreach ($payment_details['items'] as $item) {
//         $item_id_arr[] = $item['id'];
//     }

//     // Upload file
//     $file = $request->doc;
//     $file_name = Str::random(20) . '.' . $file->extension();
//     $path = 'uploads/offline_payment/' . slugify(auth()->user()->name) . '/' . $file_name;
//     FileUploader::upload($file, $path, null, null, 300);

//     // Save offline payment in table
//     $offline_payment = [
//         'user_id' => auth()->user()->id,
//         'item_type' => $request->item_type,
//         'items' => json_encode($item_id_arr),
//         'tax' => $payment_details['tax'],
//         'total_amount' => $payment_details['payable_amount'],
//         'doc' => $path,
//         'coupon' => $payment_details['coupon'] ?? null,
//     ];

//     OfflinePayment::insert($offline_payment);

//     // Handle bundle logic separately
//     if ($request->item_type == 'bundle') {
//         foreach ($item_id_arr as $bundle_id) {
//             $bundle = CourseBundle::find($bundle_id);
//             if (!$bundle) continue;

//             $payment_data = [
//                 'user_id' => auth()->user()->id,
//                 'bundle_id' => $bundle->id,
//                 'amount' => $payment_details['payable_amount'],
//                 'tax' => $payment_details['tax'],
//                 'payment_method' => 'offline',
//                 'status' => 1,
//             ];

//             if (get_user_info($bundle->user_id)->role == 'admin') {
//                 $payment_data['admin_revenue'] = $payment_details['payable_amount'];
//             } else {
//                 $payment_data['instructor_revenue'] = $payment_details['payable_amount'] * (get_settings('instructor_revenue') / 100);
//                 $payment_data['admin_revenue'] = $payment_details['payable_amount'] - $payment_data['instructor_revenue'];
//             }

//             $bundle_payment = BundlePayment::create($payment_data);

//             // Enroll user to courses in bundle
//             $courses = json_decode($bundle->course_ids, true) ?: [];
//             $expiry = now()->addDays($bundle->subscription_limit)->timestamp;

//             foreach ($courses as $course_id) {
//                 if (!Enrollment::where('user_id', auth()->user()->id)
//                         ->where('course_id', $course_id)
//                         ->where('enrollment_type', 'bundle')
//                         ->exists()) {
//                     Enrollment::insert([
//                         'user_id' => auth()->user()->id,
//                         'course_id' => $course_id,
//                         'enrollment_type' => 'bundle',
//                         'entry_date' => strtotime('now'),
//                         'expiry_date' => $expiry,
//                     ]);
//                 }
//             }
//         }
//     }

//     // Remove session items
//     $remove_session_item = ['payment_details'];
//     if (Session::has('keys')) $remove_session_item[] = 'keys';
//     if (Session::has('session_id')) $remove_session_item[] = 'session_id';
//     Session::forget($remove_session_item);

//     // Redirect based on item type
//     if ($request->item_type == 'course') {
//         CartItem::whereIn('course_id', $item_id_arr)->where('user_id', auth()->user()->id)->delete();
//         $url = 'purchase.history';
//     } elseif ($request->item_type == 'bootcamp') {
//         $url = 'bootcamps';
//     } elseif ($request->item_type == 'package') {
//         $url = 'team.packages';
//     } elseif ($request->item_type == 'tutor_booking') {
//         $url = 'tutor_list';
//     } elseif ($request->item_type == 'ebook') {
//         $url = 'ebooks';
//     } elseif ($request->item_type == 'bundle') {
//         $url = 'course.bundles';
//     } 

//     Session::flash('success', get_phrase('The payment will be completed once the admin reviews and approves it.'));
//     return redirect()->route($url);
// }


}
