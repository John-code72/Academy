<!DOCTYPE html>
<html>

<head>
    <title>Course Purchase Successful</title>
</head>

<body>
    <h2>Hello {{ $user_name }},</h2>
    <p>Thank you for purchasing the following course(s):</p>

    <ul>
        @foreach ($course_list as $course)
            <li>{{ $course['title'] ?? 'Course ID: ' . $course['id'] }}</li>
        @endforeach
    </ul>

    {{-- <p><strong>Total Paid:</strong> {{ $total_amount }}</p> --}}

    <p>We hope you enjoy your learning journey!</p>
    <br>
    <p>— The Academy Team</p>
</body>

</html>
