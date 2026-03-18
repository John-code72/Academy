<!DOCTYPE html>
<html>

<head>
    <title>Your Courses Have Been Purchased</title>
</head>

<body>
    <h2>Hello {{ $creator_name }},</h2>

    <p>Good news! The following course(s) created by you have been purchased by <strong>{{ $buyer_name }}</strong>:</p>

    <ul>
        @foreach ($course_list as $course)
            <li>
                {{ $course['title'] ?? 'Course ID: ' . $course['id'] }}
                {{-- @if (!empty($course['price']))
                    — <strong>{{ $course['price'] }}</strong>
                @endif --}}
            </li>
        @endforeach
    </ul>

    <p>Keep up the great work and continue sharing your knowledge!</p>
    <br>
    <p>— The Academy Team</p>
</body>

</html>
