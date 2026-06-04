@extends('layouts.default')
@push('title', get_phrase('Add New Ticket'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <!------------ My ticket area start  ------------>
    <section class="course-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')
                <div class="col-lg-9">

                    <div class="my-panel message-panel edit_profile mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="g-title">{{ get_phrase('Ticket Form') }}</h4>
                            <a href="{{ route('support.ticket.index') }}" class="eBtn gradient d-flex align-items-center text-nowrap"><i class="fi-rr-arrow-alt-left me-2"></i> {{ get_phrase('Back') }}</a>
                        </div>
                        <form action="{{ route('support.ticket.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @php
                                use App\Models\User;
                                // current user root admin
                                $creatorId = is_root_admin() ? auth()->user()->id : User::orderBy('id', 'asc')->value('id');
                            @endphp

                            <input type="hidden" name="admin_id" value="{{ $creatorId }}">

                            <div class="row">
                                <div class="col-lg-12 mb-20">
                                    <div class="form-group">
                                        <label for="subject" class="form-label">{{ get_phrase('Subject') }}</label>
                                        <input type="text" class="form-control" name="subject" id="subject" required>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <div class="form-group">
                                        <label for="select_category_id" class="form-label">{{ get_phrase('Category') }}</label>
                                        <select class="lms-select lms-form-control lms-md-select" name="category_id" id="select_category_id" required>
                                            <option value="">{{ get_phrase('Select a category') }}</option>
                                            @php
                                                $all_categories = App\Models\TicketCategory::where('status', 1)->get();
                                            @endphp
                                            @foreach ($all_categories as $all_category)
                                                <option value="{{ $all_category->id }}">{{ $all_category->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <div class="form-group">
                                        <label for="select_status_id" class="form-label">{{ get_phrase('Status') }}</label>
                                        <select class="lms-select lms-form-control lms-md-select" name="status_id" id="select_status_id" required>
                                            <option value="">{{ get_phrase('Select a status') }}</option>
                                            @php
                                                $all_statuses = App\Models\TicketStatus::where('status', 1)->get();
                                            @endphp
                                            @foreach ($all_statuses as $all_status)
                                                <option value="{{ $all_status->id }}">{{ $all_status->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <div class="form-group">
                                        <label for="select_priority_id" class="form-label">{{ get_phrase('Priority') }}</label>
                                        <select class="lms-select lms-form-control lms-md-select" name="priority_id" id="select_priority_id" required>
                                            <option value="">{{ get_phrase('Select a priority') }}</option>
                                            @php
                                                $all_priorities = App\Models\TicketPriority::where('status', 1)->get();
                                            @endphp
                                            @foreach ($all_priorities as $all_priority)
                                                <option value="{{ $all_priority->id }}">{{ $all_priority->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <div class="form-group">
                                        <label for="messageInput" class="form-label">{{ get_phrase('Message') }}</label>
                                        <textarea name="message" class="form-control" id="messageInput" cols="30" rows="5" placeholder="Type your message here..." required></textarea>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <div class="form-group">
                                        {{-- <label for="file" class="form-label">{{ get_phrase('File') }}</label> --}}
                                        <input type="file" name="file[]" multiple class="form-control ol-form-control" id="file">
                                    </div>
                                </div>

                            </div>
                            <button class="eBtn btn gradient mt-10">{{ get_phrase('Submit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!------------ My ticket area end  ------------>
@endsection
@push('js')

@endpush
