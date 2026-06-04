<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\AiCoursePersonalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;

class AiCourseGeneratorController extends Controller
{
    public function generate(Request $request, AiCoursePersonalizer $personalizer)
    {
        $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'lesson_count' => 'nullable|integer|min:3|max:12',
            'brief'        => 'nullable|string|max:2000',
        ]);

        $course = Course::findOrFail($request->course_id);

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        try {
            $result = $personalizer->generateForMainCourse(
                $course,
                (int) ($request->lesson_count ?? 6),
                $request->brief
            );
            $created = $result['created'];
            $heygen = $result['heygen'];
        } catch (Throwable $e) {
            $message = get_phrase('AI generation failed: ') . $e->getMessage();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['error' => $message]);
            }

            Session::flash('error', $message);

            return redirect()->back();
        }

        $success = get_phrase('AI curriculum generated:') . ' ' . $created . ' ' . get_phrase('lessons');
        if (!empty($heygen) && ($heygen['ready'] + $heygen['pending'] + $heygen['failed']) > 0) {
            $success .= ' — ' . get_phrase('HeyGen videos:') . ' ' . $heygen['ready'] . ' ' . get_phrase('ready');
            if ($heygen['pending'] > 0) {
                $success .= ', ' . $heygen['pending'] . ' ' . get_phrase('still generating (refresh curriculum in 2–3 min)');
            }
            if ($heygen['failed'] > 0) {
                $success .= ', ' . $heygen['failed'] . ' ' . get_phrase('failed (check HeyGen credits)');
            }
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'reload' => 1,
                'success' => $success,
            ]);
        }

        Session::flash('success', $success);

        return redirect()->route('admin.course.edit', ['id' => $course->id, 'tab' => 'curriculum']);
    }
}
