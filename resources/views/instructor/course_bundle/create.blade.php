@extends('layouts.instructor')
@push('title', get_phrase('Create crouse bundle'))

@section('content')
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="ol-card radius-8px">
                <div class="ol-card-body my-3 py-4 px-20px">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                        <h4 class="title fs-16px">
                            <i class="fi-rr-settings-sliders me-2"></i>
                            {{ get_phrase('Add course bundle') }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="ol-card p-3">
                <div class="ol-card-body">
                    <form action="{{ route('instructor.course.bundle.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="title">{{ get_phrase('Title') }}</label>
                                <input type="text" class="form-control ol-form-control" name="title" id="title" placeholder="{{ get_phrase('Course bundle title') }}" required>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label for="course_ids" class="form-label ol-form-label">{{ get_phrase('Select Courses') }}</label>
                                @php
                                    $user_id = auth()->id();
                                    $courses = \App\Models\Course::where('user_id', $user_id)->where('status', 'active')->get();
                                @endphp
                                <select name="course_ids[]" id="select_bundle_courses" onchange="current_price_of_selected_courses(this)" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" required>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted" id="current_price_of_the_courses">
                                    {{ get_phrase('Current price of the courses is') }} {{ currency() }}
                                </span>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="price">{{ get_phrase('Bundle Price') }}</label>
                                <input type="number" class="form-control ol-form-control" name="price" id="price" placeholder="{{ get_phrase('Enter bundle price') }}" required>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="subscription_limit">{{ get_phrase('Subscription renew days') }}</label>
                                <input type="number" class="form-control ol-form-control" name="subscription_limit" id="subscription_limit" placeholder="{{ get_phrase('Enter subscription renew days') }}" required>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label class="form-label
                                    ol-form-label" for="thumbnail">{{ get_phrase('Thumbnail') }}</label>
                                <input type="file" class="form-control ol-form-control" name="thumbnail" id="thumbnail">
                            </div>
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="bundle_details">{{ get_phrase('Bundle details') }}</label>
                                <textarea name="bundle_details" class="form-control ol-form-control text_editor"></textarea>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn ol-btn-primary float-end">{{ get_phrase('Create bundle') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        "use strict";

        function current_price_of_selected_courses() {
            var selected_course_id = $('#select_bundle_courses').val();

            $.ajax({
                type: 'POST',
                url: "{{ route('instructor.course.bundle.current_price') }}",
                data: {
                    selected_course_id: selected_course_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    // Add the currency symbol to the response number
                    var currency_symbol = '₹'; // Set currency symbol here
                    $('#current_price_of_the_courses').html("{{ get_phrase('Current price of the courses is') }} " + currency_symbol + response);
                }
            });
        }
    </script>

@endpush
