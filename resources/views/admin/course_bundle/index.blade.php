@extends('layouts.admin')
@push('title', get_phrase('Course Manager'))
@section('content')
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('Manage Course Bundle') }}
                </h4>

                <a href="{{ route('admin.course.bundle.create') }}"class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                    <span class="fi-rr-plus"></span>
                    <span>{{ get_phrase('Add New Bundle') }}</span>
                </a>
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
                                        <a class="dropdown-item export-btn" href="#" onclick="downloadPDF('.print-table', 'course-bundle-list')"><i class="fi-rr-file-pdf"></i> {{ get_phrase('PDF') }}</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-btn" href="#" onclick="window.print();"><i class="fi-rr-print"></i> {{ get_phrase('Print') }}</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="custom-dropdown dropdown-filter @if (!isset($_GET) || (isset($_GET) && count($_GET) == 0))  @endif">
                                <button class="dropdown-header btn ol-btn-light">
                                    <i class="fi-rr-filter me-2"></i>
                                    {{ get_phrase('Filter') }}

                                    @if (isset($_GET) && count($_GET))
                                        <span class="text-12px">
                                            ({{ count($_GET) }})
                                        </span>
                                    @endif
                                </button>
                                <ul class="dropdown-list w-250px">
                                    <li>
                                        <form id="filter-dropdown" action="{{ route('admin.course.bundles') }}" method="get">
                                            <div class="filter-option d-flex flex-column gap-3">

                                                <div>
                                                    <label for="eDataList" class="form-label ol-form-label">{{ get_phrase('Status') }}</label>
                                                    <select class="form-control ol-form-control ol-select2" data-toggle="select2" name="status" class="ol-select-2" data-placeholder="Type to search...">
                                                        <option value="all">{{ get_phrase('All') }}
                                                        </option>

                                                        <option value="active"@if (isset($status) && $status == 'active') selected @endif>{{ get_phrase('Active') }} </option>
                                                        <option value="inactive"@if (isset($status) && $status == 'inactive') selected @endif>{{ get_phrase('Inactive') }} </option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="filter-button d-flex justify-content-end align-items-center mt-3">
                                                <button type="submit" class="ol-btn-primary">{{ get_phrase('Apply') }}</button>
                                            </div>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                            @if (isset($_GET) && count($_GET) > 0)
                                <a href="{{ route('admin.course.bundles') }}" class="me-2" data-bs-toggle="tooltip" title="{{ get_phrase('Clear') }}"><i class="fi-rr-cross-circle"></i></a>
                            @endif
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <form action="{{ route('admin.course.bundles') }}" method="get">
                                <div class="row row-gap-3">
                                    <div class="col-md-9">
                                        <div class="search-input flex-grow-1">
                                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ get_phrase('Search title or bundle owner') }}" class="ol-form-control form-control" />
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
                            @if ($course_bundles->count() > 0)
                                
                                <div class="table-responsive overflow-auto course_list overflow-auto" id="course-bundle_list">
                                    <table class="table eTable eTable-2 print-table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ get_phrase('Bundle Info') }}</th>
                                                <th scope="col">{{ get_phrase('Courses') }}</th>
                                                <th scope="col">{{ get_phrase('Subscription limit') }}</th>
                                                <th scope="col">{{ get_phrase('Price') }}</th>
                                                <th scope="col" class="print-d-none">{{ get_phrase('Status') }}</th>
                                                <th scope="col" class="print-d-none">{{ get_phrase('Options') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($course_bundles as $key => $bundle)
                                                @php
                                                    $course_ids = json_decode($bundle->course_ids);
                                                    $courses = \App\Models\Course::whereIn('id', $course_ids)->get();
                                                    $bundleOwenr = App\Models\User::find($bundle->user_id);
                                                @endphp
                                                <tr>
                                                    <td scope="row">
                                                        <p class="row-number">{{ ++$key }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="mb-1">{{ get_phrase('Bundle Owner : ') }} {{ $bundleOwenr->name ?? ''}}</p>
                                                        <a href="{{ route('admin.course.bundle.edit', $bundle->id) }}">{{ $bundle->title }}</a>
                                                    </td>
                                                    <td>
                                                        <ul class="bullet-list">
                                                            @foreach ($courses as $course)
                                                                <li>{{ $course->title }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </td>
                                                    <td>{{ $bundle->subscription_limit }} {{ get_phrase('days') }}</td>
                                                    <td>{{ currency($bundle->price) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $bundle->status }}">{{ get_phrase(ucfirst($bundle->status)) }}</span>
                                                    </td>

                                                    <td class="print-d-none">

                                                        <div class="dropdown ol-icon-dropdown ol-icon-dropdown-transparent">
                                                            <button class="btn ol-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="fi-rr-menu-dots-vertical"></span>
                                                            </button>

                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('admin.course.bundle.edit', $bundle->id) }}">{{ get_phrase('Edit') }}</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)" onclick="confirmModal('{{ route('admin.course.bundle.status', [$bundle->id, $bundle->status === 'active' ? 'inactive' : 'active']) }}');">
                                                                        {{ get_phrase($bundle->status === 'active' ? 'inactive' : 'active') }}
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item"  href="{{ route('course.bundle.details', $bundle->slug) }}" target="_blank">
                                                                        {{ get_phrase('View On Frontend') }}
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" onclick="confirmModal('{{ route('admin.course.bundle.delete', $bundle->id) }}')" href="javascript:void(0)">{{ get_phrase('Delete') }}</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="admin-tInfo-pagi d-flex justify-content-between justify-content-center align-items-center flex-wrap gr-15">
                                    <p class="admin-tInfo">
                                        {{ get_phrase('Showing') . ' ' . count($course_bundles) . ' ' . get_phrase('of') . ' ' . $course_bundles->total() . ' ' . get_phrase('data') }}
                                    </p>
                                    {{ $course_bundles->links() }}
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
