@extends('layouts.admin')
@push('title', get_phrase('Edit course bundle'))

@section('content')
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="ol-card radius-8px">
                <div class="ol-card-body my-3 py-4 px-20px">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                        <h4 class="title fs-16px">
                            <i class="fi-rr-settings-sliders me-2"></i>
                            {{ get_phrase('Edit course bundle') }}
                        </h4>
                        <div>
                            <a href="{{ url()->previous() }}" class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                                <i class="fi-rr-arrow-left me-1"></i> {{ get_phrase('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ol-card p-3">
                <div class="ol-card-body">
                    <form action="{{ route('admin.course.bundle.update', $course_bundle->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="title">{{ get_phrase('Title') }}</label>
                                <input type="text" class="form-control ol-form-control" name="title" id="title" value="{{ $course_bundle->title }}" required>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label for="course_ids" class="form-label ol-form-label">{{ get_phrase('Select Courses') }}</label>
                                @php
                                    $user_id = auth()->id();
                                    $courses = \App\Models\Course::where('user_id', $user_id)->where('status', 'active')->get();
                                    $current_course_ids = json_decode($course_bundle->course_ids, true);
                                @endphp
                                <select name="course_ids[]" id="select_bundle_courses" onchange="current_price_of_selected_courses(this)" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" required>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" {{ in_array($course->id, $current_course_ids) ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-muted" id="current_price_of_the_courses">
                                    {{ get_phrase('Current price of the courses is') }} {{ currency() }}
                                </span>
                            </div>

                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="price">{{ get_phrase('Bundle Price') }}</label>
                                <input type="number" class="form-control ol-form-control" name="price" id="price" value="{{ $course_bundle->price }}" required>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="subscription_limit">{{ get_phrase('Subscription renew days') }}</label>
                                <input type="number" class="form-control ol-form-control" name="subscription_limit" id="subscription_limit" value="{{ $course_bundle->subscription_limit }}" required>
                            </div>

                            <div class="fpb-7 mb-3">
                                <label for="banner" class="form-label ol-form-label">{{ get_phrase('Thumbnail') }}</label>
                                <div class="col-sm-10">
                                    <input type="file" name="thumbnail" class="form-control ol-form-control" id="banner" />
                                </div>
                            </div>
                            <div class="fpb-7 mb-3">
                                <label class="form-label ol-form-label" for="bundle_details">{{ get_phrase('Bundle details') }}</label>
                                <textarea name="bundle_details" class="form-control ol-form-control text_editor">{{ $course_bundle->bundle_details }}</textarea>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn ol-btn-primary float-end">{{ get_phrase('Update bundle') }}</button>
                            </div>
                            <div class="tab-content w-100">
                                @include('admin.course_bundle.edit_seo')
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

        $(document).ready(function() {
            current_price_of_selected_courses();
        });

        function current_price_of_selected_courses() {
            var selected_course_id = $('#select_bundle_courses').val();

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.course.bundle.current_price') }}",
                data: {
                    selected_course_id: selected_course_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    var currency_symbol = '₹';
                    $('#current_price_of_the_courses').html("{{ get_phrase('Current price of the courses is') }} " + currency_symbol + response);
                }
            });
        }
    </script>
@endpush
