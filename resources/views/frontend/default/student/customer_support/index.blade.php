@extends('layouts.default')
@push('title', get_phrase('Customer Support'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <section class="wishlist-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9">
                    <div class="d-flex align-items-center mb-5 gap-3 justify-content-between flex-wrap flex-sm-nowrap">
                        <h4 class="g-title">{{ get_phrase('Ticket List') }}</h4>
                        <a href="{{ route('support.ticket.create') }}" class="eBtn gradient text-nowrap"><i class="fi fi-rr-plus"></i> {{ get_phrase('Add New Ticket') }}</a>
                    </div>
                    <div class="my-panel">

                        @if ($tickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table eTable">
                                    <thead>
                                        <tr>
                                            <th>{{ get_phrase('Subject') }}</th>
                                            <th>{{ get_phrase('Status') }}</th>
                                            <th>{{ get_phrase('Priority') }}</th>
                                            <th>{{ get_phrase('Category') }}</th>
                                            <th>{{ get_phrase('Options') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets as $ticket)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('support.ticket.message', $ticket->code) }}">{{ $ticket->subject }}</a>
                                                </td>
                                                <td>{{ $ticket->status->title }}</td>
                                                <td>{{ $ticket->priority->title }}</td>
                                                <td>{{ $ticket->category->title }}</td>
                                                <td>
                                                    <a href="{{ route('support.ticket.message', $ticket->code) }}" class="d-flex align-items-center justify-content-center btn btn-primary text-18 text-white py-3" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('View Ticket') }}">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M3 21.75C2.903 21.75 2.80589 21.731 2.71289 21.693C2.43289 21.577 2.25 21.303 2.25 21V6C2.25 3.582 3.582 2.25 6 2.25H18C20.418 2.25 21.75 3.582 21.75 6V15C21.75 17.418 20.418 18.75 18 18.75H6.31104L3.53101 21.53C3.38701 21.674 3.195 21.75 3 21.75ZM6 3.75C4.423 3.75 3.75 4.423 3.75 6V19.189L5.46997 17.469C5.61097 17.328 5.801 17.249 6 17.249H18C19.577 17.249 20.25 16.576 20.25 14.999V5.99902C20.25 4.42202 19.577 3.74902 18 3.74902H6V3.75ZM16.75 8.5C16.75 8.086 16.414 7.75 16 7.75H8C7.586 7.75 7.25 8.086 7.25 8.5C7.25 8.914 7.586 9.25 8 9.25H16C16.414 9.25 16.75 8.914 16.75 8.5ZM13.75 12.5C13.75 12.086 13.414 11.75 13 11.75H8C7.586 11.75 7.25 12.086 7.25 12.5C7.25 12.914 7.586 13.25 8 13.25H13C13.414 13.25 13.75 12.914 13.75 12.5Z"
                                                                fill="#fff" />
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="row bg-white radius-10 mx-2">
                                <div class="com-md-12">
                                    @include('frontend.default.empty')
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if (count($tickets) > 0)
                <div class="entry-pagination">
                    <nav aria-label="Page navigation example">
                        {{ $tickets->links() }}
                    </nav>
                </div>
            @endif
            <!-- Pagination -->
        </div>
    </section>
    <!------------ purchase history area End  ------------>
@endsection
@push('js')@endpush
