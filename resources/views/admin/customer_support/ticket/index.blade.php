@extends('layouts.admin')
@push('title', get_phrase('Customer Support | Tickets'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
<!-- Mani section header and breadcrumb -->
<div class="ol-card radius-8px">
    <div class="ol-card-body my-3 py-12px px-20px">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
            <h4 class="title fs-16px">
                <i class="fi-rr-settings-sliders me-2"></i>
                {{ get_phrase('Tickets') }} <span class="text-muted"></span>
            </h4>

            <a href="{{ route('admin.customer.support.ticket.create') }}" class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                <span class="fi-rr-plus"></span>
                <span>{{ get_phrase('Add New Ticket') }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Start Admin area -->
<div class="row">
    <div class="col-12">
        <div class="ol-card">
            <div class="ol-card-body p-3">

                <div class="row print-d-none mb-3 mt-3 row-gap-3">
                    <div class="col-md-6  pt-2 pt-md-0">
                        <div class="custom-dropdown">
                            <button class="dropdown-header btn ol-btn-light">
                                {{ get_phrase('Export') }}
                                <i class="fi-rr-file-export ms-2"></i>
                            </button>
                            <ul class="dropdown-list">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="downloadPDF('.print-table', 'ticket-list')"><i class="fi-rr-file-pdf"></i> {{ get_phrase('PDF') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="window.print();"><i class="fi-rr-print"></i> {{ get_phrase('Print') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('admin.customer.support.ticket.index') }}" method="get">
                            <div class="row row-gap-3">
                                <div class="col-md-9">
                                    <div class="search-input">
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ get_phrase('Search Subject') }}" class="ol-form-control form-control" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn ol-btn-primary w-100" id="submit-button">{{ get_phrase('Search') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                @if (count($tickets) > 0)
                    <div class="table-responsive course_list" id="course_list">
                        <table class="table eTable eTable-2 print-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ get_phrase('Subject') }}</th>
                                    <th scope="col">{{ get_phrase('User Name') }}</th>
                                    <th scope="col">{{ get_phrase('Status') }}</th>
                                    <th scope="col">{{ get_phrase('Priority') }}</th>
                                    <th scope="col">{{ get_phrase('Category') }}</th>
                                    <th class="print-d-none" scope="col">{{ get_phrase('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $key => $ticket)
                                    <tr>
                                        <th scope="row">
                                            <p class="row-number">{{ $key + 1 }}</p>
                                        </th>

                                        <td>
                                            {{ $ticket->subject }}
                                        </td>
                                        <td>
                                            {{ $ticket->user->name }}
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $ticket->status->color }}">{{ $ticket->status->title }}</span>
                                        </td>
                                        <td>
                                            {{ $ticket->priority->title }}
                                        </td>
                                        <td>
                                            {{ $ticket->category->title }}
                                        </td>

                                        <td class="print-d-none">
                                            <div class="dropdown ol-icon-dropdown ol-icon-dropdown-transparent">
                                                <button class="btn ol-btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="fi-rr-menu-dots-vertical"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('admin.customer.support.ticket.message', $ticket->code) }}">{{ get_phrase('View Ticket') }}</a></li>
                                                    <li><a class="dropdown-item" onclick="ajaxModal('{{ route('admin.customer.support.ticket.edit', ['id' => $ticket->id]) }}', '{{ get_phrase('Edit ticket') }}')" href="#">{{ get_phrase('Edit') }}</a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="confirmModal('{{ route('admin.customer.support.ticket.delete', $ticket->id) }}')">{{ get_phrase('Delete') }}</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    @include('admin.no_data')
                @endif
                <!-- Data info and Pagination -->
                @if (count($tickets) > 0)
                    <div class="admin-tInfo-pagi d-flex justify-content-between justify-content-center align-items-center flex-wrap gr-15">
                        <p class="admin-tInfo">
                            {{ get_phrase('Showing') . ' ' . count($tickets) . ' ' . get_phrase('of') . ' ' . $tickets->total() . ' ' . get_phrase('data') }}
                        </p>
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- End Admin area -->
@endsection
@push('js')
@endpush
