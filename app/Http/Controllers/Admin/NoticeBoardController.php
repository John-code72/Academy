<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoticeBoard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NoticeBoardController extends Controller
{

    public function create_notice(Request $request)
    {

        return view('admin.course.notice.create_notice_board');
    }

    public function notice(Request $request)
    {

        return view('admin.course.notice.notice');
    }


    public function store_notice(Request $request, $course_id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $notice = NoticeBoard::create([
            'course_id'   => $course_id,
            'title'       => $request->title,
            'description' => $request->description,
            'is_urgent'   => $request->has('is_urgent') ? 1 : 0,
        ]);

        if ($request->has('is_urgent')) {
            $this->sendUrgentMail($notice);
        }

        return redirect()->back()->with('success', 'Notice added successfully');
    }

    private function sendUrgentMail($notice)
    {
        $emails = User::join('enrollments', 'enrollments.user_id', '=', 'users.id')
            ->where('enrollments.course_id', $notice->course_id)
            ->where('users.role', 'student')
            ->whereNotNull('users.email')
            ->pluck('users.email')
            ->unique();

        foreach ($emails as $email) {

            Mail::raw(strip_tags($notice->description), function ($message) use ($email, $notice) {
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($email)
                    ->subject('Urgent Notice: ' . $notice->title);
            });
        }
    }

    public function resend_notice($id)
    {
        $notice = NoticeBoard::findOrFail($id);
        $this->sendUrgentMail($notice);

        return redirect()->back()->with('success', 'Notice resent successfully');
    }


    public function delete_notice($id)
    {
        $notice = NoticeBoard::find($id);
        $notice->delete();

        return redirect()->back()->with('success', get_phrase('Notice deleted successfully'));
    }

    public function edit_notice($id)
    {
        $notice = NoticeBoard::findOrFail($id);
        return view('admin.course.notice.edit_notice', compact('notice'));
    }

    public function update_notice(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $notice = NoticeBoard::findOrFail($id);
        $notice->update($request->only(['title', 'description']));

        return redirect()->back()->with('success', 'Notice updated successfully');
    }
}
