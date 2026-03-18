@extends('layouts.admin')
@push('title', get_phrase('Subscription Report'))

@section('content')

    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('Subscription Report') }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Start Admin area -->
    <div class="row">
        <div class="col-12">
            <div class="ol-card">
                <div class="ol-card-body p-3 mb-5">
                    <div class="row mt-3 mb-4">
                        <div class="col-md-6 d-flex align-items-center gap-3">
                            <div class="custom-dropdown ms-2">
                                <button class="dropdown-header btn ol-btn-light">
                                    {{ get_phrase('Export') }}
                                    <i class="fi-rr-file-export ms-2"></i>
                                </button>
                                <ul class="dropdown-list">
                                    <li>
                                        <a class="dropdown-item export-btn" href="#" onclick="downloadPDF('.print-table', 'subscription-report')"><i class="fi-rr-file-pdf"></i> {{ get_phrase('PDF') }}</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-btn" href="#" onclick="window.print();"><i class="fi-rr-print"></i> {{ get_phrase('Print') }}</a>
                                    </li>
                                </ul>
                            </div>


                        </div>



                        <div class="col-md-6 mt-3 mt-md-0">
                            <form action="{{ route('admin.course.bundle.subscription.report') }}" method="get">
                                <div class="row row-gap-3">
                                    <div class="col-md-9">
                                        <div class="search-input flex-grow-1">
                                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ get_phrase('Search Title') }}" class="ol-form-control form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn ol-btn-primary w-100" id="submit-button">{{ get_phrase('Search') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if ($bundle_histories->count() > 0)
                                <div class="table-responsive overflow-auto course_list overflow-auto" id="course-bundle_list">
                                    <table class="table eTable eTable-2 print-table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ get_phrase('User') }}</th>
                                                <th scope="col">{{ get_phrase('Bundle') }}</th>
                                                <th scope="col">{{ get_phrase('Included courses') }}</th>
                                                <th scope="col">{{ get_phrase('Date') }}</th>
                                                <th scope="col">{{ get_phrase('Amount') }}</th>
                                                <th scope="col">{{ get_phrase('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bundle_histories as $key => $bundle)
                                                @php
                                                    $courseBundle = \App\Models\CourseBundle::find($bundle->bundle_id);
                                                    $courseIds = json_decode($courseBundle?->course_ids ?? '[]', true);
                                                    $courses = \App\Models\Course::whereIn('id', $courseIds)->pluck('title');
                                                    $purchase_date = $bundle->created_at->timestamp;
                                                    $expire_date = $courseBundle ? strtotime("+{$courseBundle->subscription_limit} days", $purchase_date) : null;

                                                    $current_time = time();
                                                    $status = $expire_date && $expire_date > $current_time ? 'Active' : 'Expired';
                                                @endphp
                                                <tr>
                                                    <td scope="row">
                                                        <p class="row-number">{{ ++$key }}</p>
                                                    </td>
                                                    <td>
                                                        {{ \App\Models\User::find($bundle->user_id)?->name ?? 'Unknown' }}
                                                    </td>
                                                    <td>
                                                        {{ $courseBundle?->title }}
                                                    </td>
                                                    <td>
                                                        <ul class="bullet-list">
                                                            @foreach ($courses as $course)
                                                                <li>{{ $course }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <p class="m-1">{{ get_phrase('Purchase Date') }}: <b>{{ date('d M Y', $purchase_date) }}</b></p>
                                                        <p class="m-1">{{ get_phrase('Expire Date') }}: <b class="text-success">{{ date('d M Y', $expire_date) }}</b></p>
                                                    </td>
                                                    <td>
                                                        {{ currency($bundle->amount) }}
                                                    </td>
                                                    <td>
                                                        @if ($status == 'Active')
                                                            <span class="badge bg-success">{{ get_phrase('Active') }}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ get_phrase('Expired') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="admin-tInfo-pagi d-flex justify-content-between justify-content-center align-items-center flex-wrap gr-15">
                                    <p class="admin-tInfo">
                                        {{ get_phrase('Showing') . ' ' . count($bundle_histories) . ' ' . get_phrase('of') . ' ' . $bundle_histories->total() . ' ' . get_phrase('data') }}
                                    </p>
                                    {{ $bundle_histories->links() }}
                                </div>
                            @else
                                @include('admin.no_data')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Admin area -->
@endsection
