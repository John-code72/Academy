<?php

namespace App\Models;


use App\Mail\CourseBundleMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use DB;


class BundlePayment extends Model
{

    protected $fillable = ['user_id', 'bundle_id', 'payment_method', 'payment_details', 'amount', 'admin_revenue', 'instructor_revenue', 'tax', 'status'];

    public function courseBundle()
    {
        return $this->belongsTo(CourseBundle::class, 'bundle_id', 'id');
    }

    public static function purchase_bundle($identifier)
    {
        // get payment details
        $payment_details = session('payment_details');

        if (Session::has('keys')) {
            $transaction_keys = session('keys');
            $transaction_keys = json_encode($transaction_keys);
            $payment_data['payment_details'] = $transaction_keys;
            $remove_session_item[] = 'keys';
        }

        if (Session::has('session_id')) {
            $transaction_keys = session('session_id');
            $payment_data['payment_details'] = $transaction_keys;
            $remove_session_item[] = 'session_id';
        }

        // generate invoice for payment
        $payment_data['user_id'] = auth()->user()->id;
        $payment_data['bundle_id'] = $payment_details['items'][0]['id'];
        $payment_data['amount'] = $payment_details['payable_amount'];
        $payment_data['tax'] = $payment_details['tax'];
        $payment_data['payment_method'] = $identifier;
        $payment_data['status'] = 1;

        $bundle = CourseBundle::find($payment_details['items'][0]['id']);
        if (get_user_info($bundle->user_id)->role == 'admin') {
            $payment_data['admin_revenue'] = $payment_details['payable_amount'];
        } else {
            $payment_data['instructor_revenue'] = $payment_details['payable_amount'] * (get_settings('instructor_revenue') / 100);
            $payment_data['admin_revenue'] = $payment_details['payable_amount'] - $payment_data['instructor_revenue'];
        }

        // insert payment details
        $bundle_payment = BundlePayment::create($payment_data);

        $bundle = CourseBundle::find($bundle_payment->bundle_id);
        $courses = $bundle ? json_decode($bundle->course_ids, true) : [];
        $expiry = now()->addDays($bundle->subscription_limit)->timestamp;

        $enroll['user_id'] = auth()->user()->id;
        foreach ($courses as $course) {
            $enroll['course_id'] = $course;
            $enroll['enrollment_type'] = 'bundle';
            $enroll['entry_date'] = strtotime('now');
            $enroll['expiry_date'] = $expiry;

            if (!Enrollment::where('user_id', $enroll['user_id'])->where('course_id', $course)->where('enrollment_type', 'bundle')->exists()) {
                Enrollment::insert($enroll);
            }
        }

        $mail_data['bundle'] = $bundle;
        $mail_data['invoice'] = BundlePayment::join('course_bundles', 'bundle_payments.bundle_id', 'course_bundles.id')
            ->where('bundle_payments.bundle_id', $bundle->id)
            ->where('bundle_payments.user_id', auth()->user()->id)
            ->select('bundle_payments.*', 'course_bundles.title', 'course_bundles.slug')->first();


        $mail_data['subject'] = "Course Bundle Mail";
        $mail_data['description'] = "You have successfully purchased the bundle: {$bundle->title}";

        self::send_mail($mail_data);

        $remove_session_item[] = 'payment_details';
        Session::forget($remove_session_item);
        Session::flash('success', 'Bundle purchased successfully.');
        return redirect()->route('course.bundles');
    }

//     public static function purchase_bundle($identifier)
// {
//     // get payment details
//     $payment_details = session('payment_details');

//     if (Session::has('keys')) {
//         $transaction_keys = session('keys');
//         $transaction_keys = json_encode($transaction_keys);
//         $payment_data['payment_details'] = $transaction_keys;
//         $remove_session_item[] = 'keys';
//     }

//     if (Session::has('session_id')) {
//         $transaction_keys = session('session_id');
//         $payment_data['payment_details'] = $transaction_keys;
//         $remove_session_item[] = 'session_id';
//     }

//     // generate invoice for payment
//     $payment_data['user_id'] = auth()->user()->id;
//     $payment_data['bundle_id'] = $payment_details['items'][0]['id'];
//     $payment_data['amount'] = $payment_details['payable_amount'];
//     $payment_data['tax'] = $payment_details['tax'];
//     $payment_data['payment_method'] = $identifier;
//     $payment_data['status'] = 1;

//     $bundle = CourseBundle::find($payment_details['items'][0]['id']);

//     if (get_user_info($bundle->user_id)->role == 'admin') {
//         $payment_data['admin_revenue'] = $payment_details['payable_amount'];
//         $payment_data['instructor_revenue'] = 0;
//     } else {
//         $payment_data['instructor_revenue'] =
//             $payment_details['payable_amount'] * (get_settings('instructor_revenue') / 100);
//         $payment_data['admin_revenue'] =
//             $payment_details['payable_amount'] - $payment_data['instructor_revenue'];
//     }

//     // insert bundle payment
//     $bundle_payment = BundlePayment::create($payment_data);

//     /*
//     |--------------------------------------------------------------------------
//     | INSERT INTO PAYMENT HISTORIES (FOR BUNDLE)
//     |--------------------------------------------------------------------------
//     */
//     // $payment_history = [];
//     // $payment_history['user_id']      = auth()->user()->id;
//     // $payment_history['bundle_id']    = $bundle->id;
//     // $payment_history['course_id']    = null;
//     // $payment_history['amount']       = $payment_details['payable_amount'];
//     // $payment_history['tax']          = $payment_details['tax'];
//     // $payment_history['payment_type'] = $identifier;
//     // $payment_history['coupon']       = $payment_details['coupon'] ?? null;
//     // $payment_history['invoice']      = Str::random(20);
//     // $payment_history['admin_revenue'] = $payment_data['admin_revenue'];
//     // $payment_history['instructor_revenue'] = $payment_data['instructor_revenue'];

//     // if (Session::has('keys')) {
//     //     $payment_history['transaction_id'] = json_encode(session('keys'));
//     // }

//     // if (Session::has('session_id')) {
//     //     $payment_history['session_id'] = session('session_id');
//     // }

//     // DB::table('payment_histories')->insert($payment_history);

//     /*
//     |--------------------------------------------------------------------------
//     | ENROLL COURSES UNDER BUNDLE
//     |--------------------------------------------------------------------------
//     */
//     $courses = $bundle ? json_decode($bundle->course_ids, true) : [];
//     $expiry = now()->addDays($bundle->subscription_limit)->timestamp;

//     $enroll['user_id'] = auth()->user()->id;

//     foreach ($courses as $course) {
//         $enroll['course_id'] = $course;
//         $enroll['enrollment_type'] = 'bundle';
//         $enroll['entry_date'] = time();
//         $enroll['expiry_date'] = $expiry;

//         if (!Enrollment::where('user_id', $enroll['user_id'])
//             ->where('course_id', $course)
//             ->where('enrollment_type', 'bundle')
//             ->exists()) {
//             Enrollment::insert($enroll);
//         }
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | SEND MAIL
//     |--------------------------------------------------------------------------
//     */
//     $mail_data['bundle'] = $bundle;
//     $mail_data['invoice'] = BundlePayment::join(
//             'course_bundles',
//             'bundle_payments.bundle_id',
//             'course_bundles.id'
//         )
//         ->where('bundle_payments.bundle_id', $bundle->id)
//         ->where('bundle_payments.user_id', auth()->user()->id)
//         ->select('bundle_payments.*', 'course_bundles.title', 'course_bundles.slug')
//         ->first();

//     $mail_data['subject'] = "Course Bundle Mail";
//     $mail_data['description'] = "You have successfully purchased the bundle: {$bundle->title}";

//     self::send_mail($mail_data);

//     $remove_session_item[] = 'payment_details';
//     Session::forget($remove_session_item);

//     Session::flash('success', 'Bundle purchased successfully.');
//     return redirect()->route('course.bundles');
// }





    public static function send_mail($mail_data)
    {
        config([
            'mail.mailers.smtp.transport' => get_settings('protocol'),
            'mail.mailers.smtp.host' => get_settings('smtp_host'),
            'mail.mailers.smtp.port' => get_settings('smtp_port'),
            'mail.mailers.smtp.encryption' => get_settings('smtp_crypto'),
            'mail.mailers.smtp.username' => get_settings('smtp_from_email'),
            'mail.mailers.smtp.password' => get_settings('smtp_pass'),
            'mail.from.address' => get_settings('smtp_from_email'),
            'mail.from.name' => get_settings('smtp_user'),
        ]);

        $send = Mail::to(auth()->user()->email)->send(new CourseBundleMail($mail_data));
        return $send;
    }


}
