@php
    $course_ids = json_decode($bundle->course_ids);
    $courses = \App\Models\Course::whereIn('id', $course_ids)->get();
    $user = auth()->user();
@endphp

<x-mail::message>

{{-- Header --}}
<div style="text-align:center; margin-bottom: 30px;">
    <h1 style="margin-bottom: 5px;">{{ get_phrase('Invoice') }}</h1>
    <p style="color: #6c757d; margin: 0;">
        {{ get_phrase('Thank you for your purchase') }}
    </p>
</div>

{{-- Invoice Summary --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
    <tr>
        <td width="50%" style="padding: 10px 0;">
            <strong>{{ get_phrase('Billed To') }}</strong><br>
            {{ $user->name }}<br>
            {{ $user->email }}
        </td>
        <td width="50%" style="padding: 10px 0; text-align: right;">
            <strong>{{ get_phrase('Invoice Date') }}:</strong>
            {{ date('d M, Y') }}<br>
            <strong>{{ get_phrase('Invoice Total') }}:</strong>
            <span style="font-size: 18px; font-weight: bold;">
                ${{ number_format($bundle->price, 2) }}
            </span>
        </td>
    </tr>
</table>

<hr style="border: none; border-top: 1px solid #eaeaea; margin: 20px 0;">

{{-- Bundle Details --}}
<h3 style="margin-bottom: 10px;">{{ get_phrase('Course Bundle Details') }}</h3>

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f8f9fa;">
            <th align="left" style="padding: 12px; border: 1px solid #dee2e6;">
                {{ get_phrase('Bundle Name') }}
            </th>
            <th align="left" style="padding: 12px; border: 1px solid #dee2e6;">
                {{ get_phrase('Included Courses') }}
            </th>
            <th align="right" style="padding: 12px; border: 1px solid #dee2e6;">
                {{ get_phrase('Price') }}
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 12px; border: 1px solid #dee2e6;">
                {{ $bundle->title }}
            </td>
            <td style="padding: 12px; border: 1px solid #dee2e6;">
                <ul style="padding-left: 18px; margin: 0;">
                    @foreach ($courses as $course)
                        <li>{{ $course->title }}</li>
                    @endforeach
                </ul>
            </td>
            <td align="right" style="padding: 12px; border: 1px solid #dee2e6;">
                ${{ number_format($bundle->price, 2) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- Footer --}}
<div style="margin-top: 30px;">
    <p style="margin-bottom: 5px;">
        {{ get_phrase('If you have any questions, feel free to contact our support team.') }}
    </p>
</div>

</x-mail::message>
