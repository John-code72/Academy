<!DOCTYPE html>
<html>

<head>
    <title>New Course Purchase Notification</title>
</head>

<body>
    <h2>Hello {{ $admin_name }},</h2>
    <p>A new purchase has been made on your site.</p>

    <p><strong>Buyer:</strong> {{ $buyer_name }}</p>
    {{-- <p><strong>Total Paid:</strong> {{ $total_amount }}</p> --}}

    <p>The following course(s) were purchased:</p>
    <ul>
        @foreach ($course_list as $course)
            <li>{{ $course['title'] ?? 'Course ID: ' . $course['id'] }}</li>
        @endforeach
    </ul>

    <p>Regards,<br>The Academy System</p>
</body>

</html>
