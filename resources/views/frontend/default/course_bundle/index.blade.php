@extends('layouts.default')
@push('title', get_phrase('Course Bundles'))
@push('meta')@endpush

<style>
.eNtery-item {
    margin-top: -110px !important;
}
</style>

@section('content')
    <!------------------- Breadcum Area Start  ------>
    <section class="breadcum-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="eNtry-breadcum mt-5">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">{{ get_phrase('Home') }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ get_phrase('Course Bundles') }}</li>
                            </ol>
                        </nav>
                        <h3 class="g-title">{{ get_phrase('Course Bundles') }}</h3>
                    </div>
                </div>
            </div>
            <div class="row align-items-center mt-3">
                <div class="col-lg-8 col-md-6 col-sm-6 col-6">
                    <p class="showing-text">
                        {{ get_phrase('Showing') . ' ' . count($course_bundles) . ' ' . get_phrase('of') . ' ' . $course_bundles->total() . ' ' . get_phrase('data') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!------------------- Breadcum Area End  --------->



    <!-------------- List Item Start   --------------->
    <div class="eNtery-item">
        <div class="container">
            <!-- Items -->
            <div class="row">
                @foreach ($course_bundles as $bundle)
                    @php
                        $instructor_details = App\Models\User::find($bundle->user_id);
                        $course_ids = json_decode($bundle->course_ids);
                        sort($course_ids);
                    @endphp
                    <div class="col-lg-6 mb-4">
                        <div class="course-bundle-item">
                            <div class="bundle-header d-flex justify-content-between align-items-center flex-wrap px-0 pt-2">
                                <a href="{{ route('course.bundle.details', $bundle->slug) }}">
                                    <div class="bundle-head d-flex align-items-center gap-4">
                                        <h4 class="title">{{ $bundle->title }}</h4> |
                                        <p class="course-count">{{ count($course_ids) }} {{ get_phrase('courses') }}</p>
                                    </div>
                                </a>
                                <p class="price">{{ currency($bundle->price) }}</p>
                            </div>

                            <div class="bundle-body">
                                <ul class="course-bundle">
                                    @php $total_courses_price = 0; @endphp
                                    @foreach ($course_ids as $key => $course_id)
                                        @php
                                            $course_details = App\Models\Course::where('id', $course_id)->where('status', 'active')->first();
                                            if ($course_details && !$course_details->is_free_course) {
                                                $total_courses_price += $course_details->discount_flag ? $course_details->discounted_price : $course_details->price;
                                            }
                                        @endphp
                                        @if ($course_details)
                                            <li>
                                                <div class="course-item">
                                                    <a href="{{ route('course.details', $course_details->slug, $course_details->id) }}" target="_blank">
                                                        <div class="course-content d-flex align-items-center gap-4">

                                                            <div class="course-img">
                                                                <img src="{{ get_image($course_details->thumbnail ?? '') }}" alt="" />
                                                            </div>
                                                            <h3 class="course-title">{{ $course_details->title }}</h3>
                                                        </div>
                                                    </a>
                                                    <div class="course-price">{{ currency($course_details->price) }}</div>
                                                </div>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <div class="bundle-footer">

                                <a class="bundle-details" href="{{ route('course.bundle.details', $bundle->slug) }}">{{ get_phrase('Bundle Details') }}</a>

                                @php
                                    $btn['url'] = route('purchase.course.bundle', $bundle->id);
                                    $btn['title'] = get_phrase('Buy Now');

                                    if (isset(auth()->user()->id)) {
                                        $my_bundle = App\Models\BundlePayment::where('user_id', auth()->user()->id)
                                            ->where('bundle_id', $bundle->id)
                                            ->first();

                                        if ($my_bundle) {
                                            if ($my_bundle->expiry_date && $my_bundle->expiry_date < now()) {
                                                $btn['title'] = get_phrase('Renew');
                                                $btn['url'] = route('purchase.course.bundle', $bundle->id);
                                            } else {
                                                $btn['title'] = get_phrase('My bundles');
                                                $btn['url'] = route('my.course.bundles', $bundle->slug);
                                            }
                                        }

                                        $pending_payment = DB::table('offline_payments')
                                            ->where('user_id', auth()->id())
                                            ->where('item_type', 'bundle')
                                            ->where('items', $bundle->id)
                                            ->where('status', 0)
                                            ->exists();

                                        if ($pending_payment) {
                                            $btn['title'] = get_phrase('Processing');
                                            $btn['url'] = 'javascript:void(0);';
                                        }
                                    }
                                @endphp

                                <a href="{{ $btn['url'] }}" class="buy-now">
                                    {{ $btn['title'] }}
                                </a>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if (count($course_bundles) > 0)
                <div class="entry-pagination">
                    <nav aria-label="Page navigation example">
                        {{ $course_bundles->links() }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
    <!------------iList Item End  --------------->
@endsection
@push('js')@endpush
