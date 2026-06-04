@extends('layouts.admin')
@push('title', get_phrase('Customer Support | Ticket Add'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <!-- Mani section header and breadcrumb -->
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('Add New Ticket') }} <span class="text-muted"></span>
                </h4>

                <a href="{{ route('admin.customer.support.ticket.index') }}"class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                    <span class="fi-rr-arrow-alt-left"></span>
                    <span>{{ get_phrase('Back') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-10">
        <form action="{{ route('admin.customer.support.ticket.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="ol-card">
                <h4 class="title fs-16px p-3">{{ get_phrase('Ticket Form') }}</h4>

                <div class="ol-card-body p-20px mb-3">

                    <input type="hidden" name="creator_id" value="{{ auth()->user()->id }}">

                    <div class="row mb-3">
                        <label for="subject" class="form-label ol-form-label col-sm-2 col-form-label">{{ get_phrase('Subject') }}<span class="text-danger ms-1">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" name="subject" class="form-control ol-form-control" id="subject" placeholder="{{ get_phrase('Enter your subject here') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="select_category_id" class="form-label ol-form-label col-sm-2 col-form-label">{{ get_phrase('Select Category') }}<span class="text-danger ms-1">*</span></label>
                        <div class="col-sm-10">
                            <select class="ol-select2" name="category_id" id="select_category_id" required>
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

                    <div class="row mb-3">
                        <label for="select_user_id" class="form-label ol-form-label col-sm-2 col-form-label">{{ get_phrase('Select User') }}<span class="text-danger ms-1">*</span></label>
                        <div class="col-sm-10">
                            <select class="ol-select2" name="user_id" id="select_user_id" required>
                                <option value="">{{ get_phrase('Select a user') }}</option>
                                @php
                                    $all_users = App\Models\User::whereNot('role', 'admin')->get();
                                @endphp
                                @foreach ($all_users as $all_user)
                                    <option value="{{ $all_user->id }}">{{ $all_user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="select_status_id" class="form-label ol-form-label col-sm-2 col-form-label">{{ get_phrase('Select Status') }}<span class="text-danger ms-1">*</span></label>
                        <div class="col-sm-10">
                            <select class="ol-select2" name="status_id" id="select_status_id" required>
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

                    <div class="row mb-3">
                        <label for="select_priority_id" class="form-label ol-form-label col-sm-2 col-form-label">{{ get_phrase('Select Priority') }}<span class="text-danger ms-1">*</span></label>
                        <div class="col-sm-10">
                            <select class="ol-select2" name="priority_id" id="select_priority_id" required>
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

                    <div class="row mb-3">
                        <label class="form-label ol-form-label col-sm-2 col-form-label" for="messageInput">{{ get_phrase('Message') }}<span class="text-danger ms-1">*</span></label>
                        <div class="col-sm-10">
                            <textarea class="form-control ol-form-control" id="messageInput" name="message" rows="6" placeholder="Type your message here..." required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="form-label ol-form-label col-sm-2 col-form-label" for="file">{{ get_phrase('File') }}</label>
                        <div class="col-sm-10">
                            <input type="file" name="file[]" multiple class="form-control ol-form-control" id="file">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn ol-btn-primary float-end">{{ get_phrase('Submit') }}</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection
@push('js')
@endpush
