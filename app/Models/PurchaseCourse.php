<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class PurchaseCourse extends Model
{
    use HasFactory;

    public static function purchase_course($identifier)
    {
        // get payment details
        $payment_details = session('payment_details');

        if (Session::has('keys')) {
            $transaction_keys          = session('keys');
            $transaction_keys          = json_encode($transaction_keys);
            $payment['transaction_id'] = $transaction_keys;
            $remove_session_item[]     = 'keys';
        }
        if (Session::has('session_id')) {
            $transaction_keys      = session('session_id');
            $payment['session_id'] = $transaction_keys;
            $remove_session_item[] = 'session_id';
        }

        // generate invoice for payment
        $payment['invoice'] = Str::random(20);
        for ($i = 0; $i < count($payment_details['items']); $i++) {
            $price           = $payment_details['items'][$i]['price'];
            $course_discount = $payment_details['items'][$i]['discount_price'];
            $baseAmount  = $course_discount ? $course_discount : $price;

            if (get_course_creator_id($payment_details['items'][$i]['id'])->role == 'admin') {
                $payment['admin_revenue'] = $payment_details['payable_amount'];
            } else {

                $payment['instructor_revenue'] = $baseAmount * (get_settings('instructor_revenue') / 100);
                $payment['admin_revenue']      = $payment_details['payable_amount'] - $payment['instructor_revenue'];
            }

            $payment['course_id']    = $payment_details['items'][$i]['id'];
            $payment['tax']          = $payment_details['tax'];
            $payment['amount']       = $baseAmount;
            $payment['user_id']      = auth()->user()->id;
            $payment['payment_type'] = $identifier;
            $payment['coupon']       = $payment_details['coupon'];

            // insert payment history
            $payment_history = DB::table('payment_histories')->insert($payment);

            // if payment has done then enroll user
            if ($payment_history) {
                $enroll['course_id']       = $payment_details['items'][$i]['id'];
                $enroll['user_id']         = $payment_details['custom_field']['gifted_user_id'] ? $payment_details['custom_field']['gifted_user_id'] : auth()->user()->id;
                $enroll['enrollment_type'] = "paid";
                $enroll['entry_date']      = time();

                $course_details = get_course_info($payment_details['items'][$i]['id']);

                if ($course_details->expiry_period > 0) {
                    $days = $course_details->expiry_period * 30;
                    $enroll['expiry_date'] = strtotime("+" . $days . " days");
                } else {
                    $enroll['expiry_date'] = null;
                }

                // insert a new enrollment
                DB::table('enrollments')->insert($enroll);
            }
        }

        // if payment and enroll has been done then remove items from cart
        if ($payment_history && $enroll) {
            $cart_items = $payment_details['custom_field']['cart_id'];
            foreach ($cart_items as $item) {
                DB::table('cart_items')->where('user_id', auth()->user()->id)->where('course_id', $item)->delete();
            }
        }

        // call mail function and pass payment details
        self::course_purchase_mail_function($payment_details);

        $remove_session_item[] = 'payment_details';
        Session::forget($remove_session_item);
        Session::flash('success', 'Course enrolled successfully.');

        return redirect()->route('my.courses');
    }


    private static function course_purchase_mail_function($payment_details)
    {
        // From email fallback
        $fromEmail = get_settings('smtp_from_email') ?: 'hello@example.com';
        $fromName  = 'Course Purchase Mail';

        // 1 Buyer mail
        $buyer_email = auth()->user()->email;

        if (filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::send('admin.mail.course_purchase_mail_student', [
                    'user_name' => auth()->user()->name,
                    'course_list' => $payment_details['items'],
                    'total_amount' => $payment_details['payable_amount'],
                ], function ($message) use ($buyer_email, $fromEmail, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($buyer_email)
                        ->subject('Course Purchase Successful');
                });
            } catch (\Exception $e) {
                // SMTP authentication fail error catch
                \Log::error("Buyer mail failed: " . $e->getMessage());
            }
        }

        // 2 Instructor mails (grouped by creator)
        $creator_courses = [];

        foreach ($payment_details['items'] as $item) {
            $course = DB::table('courses')->where('id', $item['id'])->first();

            if ($course) {
                // main creator
                if ($course->user_id) {
                    $creator_courses[$course->user_id][] = $item;
                }
                // multiple instructors
                if (!empty($course->instructor_ids)) {
                    $instructor_ids = json_decode($course->instructor_ids, true);
                    if (is_array($instructor_ids)) {
                        foreach ($instructor_ids as $instructor_id) {
                            $creator_courses[$instructor_id][] = $item;
                        }
                    }
                }
            }
        }

        // Remove duplicates
        foreach ($creator_courses as $key => $courses) {
            $creator_courses[$key] = array_unique($courses, SORT_REGULAR);
        }

        // Send mail per instructor
        foreach ($creator_courses as $creator_id => $courses) {
            $creator = DB::table('users')->where('id', $creator_id)->first();

            if ($creator && filter_var($creator->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::send('admin.mail.course_purchase_mail_instructor', [
                        'creator_name' => $creator->name,
                        'buyer_name' => auth()->user()->name,
                        'course_list' => $courses,
                    ], function ($message) use ($creator, $fromEmail, $fromName) {
                        $message->from($fromEmail, $fromName);
                        $message->to($creator->email)
                            ->subject('Your Courses Have Been Purchased');
                    });
                } catch (\Exception $e) {
                    \Log::error("Instructor mail failed (ID: {$creator_id}): " . $e->getMessage());
                }
            }
        }

        // 3 Root admin mail
        $rootAdmin = User::orderBy('id', 'asc')->first();

        if ($rootAdmin && filter_var($rootAdmin->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::send('admin.mail.course_purchase_mail_rootAdmin', [
                    'admin_name' => $rootAdmin->name,
                    'buyer_name' => auth()->user()->name,
                    'course_list' => $payment_details['items'],
                    'total_amount' => $payment_details['payable_amount'],
                ], function ($message) use ($rootAdmin, $fromEmail, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($rootAdmin->email)
                        ->subject('New Course Purchase Notification');
                });
            } catch (\Exception $e) {
                \Log::error("Admin mail failed: " . $e->getMessage());
            }
        }
    }
}
