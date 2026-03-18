@extends('layouts.admin')
@push('title', get_phrase('Customer Support | View Ticket'))
@push('meta')@endpush
@push('css')
@endpush
@section('content')
    <style>
        .comment-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
        }

        .comment-img {
            border-radius: 10px;
            width: 130px;
            height: 110px;
        }

        .messenger-body {
            padding: 20px 20px 40px 20px;
            border-bottom: 0 !important;
            /* height: 300px; */
            height: calc(100vh - 358px);
            overflow-y: auto;
        }

        .divider {
            border-right: 1px solid #DBDFEB;
        }

        .sidebar-title {
            font-weight: 600;
            font-size: 14px;
            color: #212534;
        }

        .text-grey {
            font-weight: 500;
            font-size: 14px;
            color: #6D718C;
        }

        .name {
            font-size: 14px;
            font-weight: 500;
            color: #212534;
        }

        .message-name {
            font-size: 16px;
            font-weight: 600;
            color: #212534;
        }

        .open {
            color: #17C653;
        }

        .not_begin {
            color: #f8285a;
        }

        .done {
            color: blue;
        }

        #send-button {
            padding: 10.5px 16px;
        }

        .cg-6px {
            column-gap: 6px;
        }

        .custom-card {
            width: 12rem;
            height: 8rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            overflow: hidden;
        }

        .custom-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .custom-card .card-body {
            padding: 10px;
        }

        .custom-card .card-text {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .div-1 {
            border-top: 1px solid #DBDFEB;
            width: 100%;
            margin: 20px 0;
        }

        .div-2 {
            border-top: 1px solid #DBDFEB;
            margin: 0 20px 0 20px;
        }

        .back-btn-msg {
            margin-right: 20px;
        }

        .upload-container .drop-zone {
            border: none;
            padding: 0;
            margin-bottom: 9px;
        }

        #messageInput {
            border: 1px solid #DBDFEB;
        }


        .macro-section {
            height: calc(100vh - 205px);
            /* height: 300px; */
            overflow-y: auto;
            padding-right: 12px;
        }


        .macro-section::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }

        .macro-section::-webkit-scrollbar-track {
            border-radius: 20px;
            background-color: #dfdfeb;
        }

        .macro-section::-webkit-scrollbar-track:hover {
            background-color: #dfdfeb;
        }

        .macro-section::-webkit-scrollbar-track:active {
            background-color: #dfdfeb;
        }

        .macro-section::-webkit-scrollbar-thumb {
            border-radius: 20px;
            background-color: #a4a8bc;
        }

        .macro-section::-webkit-scrollbar-thumb:hover {
            background-color: #a4a8bc;
        }

        .macro-section::-webkit-scrollbar-thumb:active {
            background-color: #a4a8bc;
        }


        @media screen and (max-width: 767px) {
            .divider {
                border: 0;
            }
        }

        .file-list {
            list-style: none;
            margin-top: 10px;
            padding: 0;
        }

        .file-list li {
            background: #f5f5f5;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-list li svg {
            width: 16px;
            height: 16px;
        }
    </style>
    <style>
        .custome-modal img {
            max-width: 100%;
            width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center;
            margin: 0 auto;
        }
    </style>

    <div class="messenger-area">
        <div class="messenger-header">
            <div class="user-wrap">
                <div class="name-status">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-lg-nowrap">
                        <div class="top-bar flex-wrap d-flex align-items-center gap-3">
                            <a href="{{ route('admin.customer.support.ticket.index') }}"class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                                <span class="fi-rr-arrow-alt-left"></span>
                                <span>{{ get_phrase('Back') }}</span>
                            </a>
                            <h4 class="title fs-16px">
                                {{ $ticket_details->subject }}
                            </h4>
                        </div>

                        <div class="top-bar flex-wrap d-flex align-items-center">
                            <a href="{{ route('admin.customer.support.ticket.create') }}" class="btn ol-btn-outline-secondary d-flex align-items-center cg-10px">
                                <span class="fi-rr-plus"></span>
                                <span>{{ get_phrase('Add New Ticket') }}</span>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.customer.support.ticket.message.store') }}" method="post" id="main-form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 divider">
                    <div class="mx-4 my-4 macro-section">
                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="sidebar-title ">{{ get_phrase('Ticket #') }}</h6>
                            <p class="text-grey">{{ $ticket_details->code }}</p>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="sidebar-title ">{{ get_phrase('Ticket Type') }}</h6>
                            <p>
                                <span class="open">{{ $ticket_details->status?->title ?? get_phrase('New') }}</span>
                            </p>
                        </div>
                        @isset($ticket_details->creator_id)
                            @php
                                $assigned_to = App\Models\User::where('id', $ticket_details->creator_id)->first();
                            @endphp
                            <div class="d-flex justify-content-between mb-4">
                                <h6 class="sidebar-title">{{ get_phrase('Assigned To') }}</h6>
                                <p class="name">{{ $assigned_to->name }}</p>
                            </div>
                        @else
                            <div class="d-flex justify-content-between mb-4">
                                <h6 class="sidebar-title">{{ get_phrase('Assigned To') }}</h6>
                                <p class="name">{{ get_phrase('Unassigned') }}</p>
                            </div>
                        @endisset

                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="sidebar-title">{{ get_phrase('Submitted On') }}</h6>
                            <p class="text-grey">
                                {{ \Carbon\Carbon::parse($ticket_details->created_at)->format('d/m/Y \a\t g:i A') }}</p>
                        </div>

                        @isset($ticket_details->user_id)
                            @php
                                $submitted_by = App\Models\User::where('id', $ticket_details->user_id)->first();
                            @endphp
                            <div class="d-flex justify-content-between mb-4">
                                <h6 class="sidebar-title">{{ get_phrase('Submitted By') }} </h6>
                                <p class="name">{{ $submitted_by->name }}</p>
                            </div>
                            <input type="hidden" id="userName" value="{{ $submitted_by->name }}">
                        @else
                            <div class="d-flex justify-content-between mb-4">
                                <h6 class="sidebar-title">{{ get_phrase('Submitted By') }}</h6>
                                <p class="name">{{ get_phrase('Unknown user') }}</p>
                            </div>
                        @endisset

                        <div class="div-1"></div>


                        <div class="my-3">

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <p> <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12.75 15.9375H5.25C2.5125 15.9375 0.9375 14.3625 0.9375 11.625V6.375C0.9375 3.6375 2.5125 2.0625 5.25 2.0625H12.75C15.4875 2.0625 17.0625 3.6375 17.0625 6.375V11.625C17.0625 14.3625 15.4875 15.9375 12.75 15.9375ZM5.25 3.1875C3.105 3.1875 2.0625 4.23 2.0625 6.375V11.625C2.0625 13.77 3.105 14.8125 5.25 14.8125H12.75C14.895 14.8125 15.9375 13.77 15.9375 11.625V6.375C15.9375 4.23 14.895 3.1875 12.75 3.1875H5.25Z"
                                            fill="#212534" />
                                        <path
                                            d="M8.99812 9.65247C8.36812 9.65247 7.73063 9.45748 7.24313 9.05998L4.89562 7.18498C4.65562 6.98998 4.61063 6.63748 4.80563 6.39748C5.00063 6.15748 5.35313 6.11248 5.59313 6.30748L7.94062 8.18248C8.51062 8.63998 9.47812 8.63998 10.0481 8.18248L12.3956 6.30748C12.6356 6.11248 12.9956 6.14998 13.1831 6.39748C13.3781 6.63748 13.3406 6.99748 13.0931 7.18498L10.7456 9.05998C10.2656 9.45748 9.62812 9.65247 8.99812 9.65247Z"
                                            fill="#212534" />
                                    </svg></p>
                                <p class="text-grey">
                                    {{ $submitted_by->email }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <select class="form-control px-14px ol-form-control ol-select2" id="macroSelect">
                                <option value="">{{ get_phrase('Apply macro') }}</option>
                                @foreach ($macros as $macro)
                                    <option value="{{ $macro->description }}">{{ $macro->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ol-card mt-5">
                            <div class="ol-card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <input type="hidden" name="ticket_id" value="{{ $ticket_details->id }}">
                                        <div class="fpb7 mb-2">
                                            <label class="form-label ol-form-label" for="subject">{{ get_phrase('Subject') }}</label>
                                            <input class="form-control ol-form-control" type="text" id="subject" name="subject" value="{{ $ticket_details->subject }}" required>
                                        </div>
                                        <div class="fpb-7 mb-3">
                                            <label class="form-label ol-form-label" for="select_user_id">{{ get_phrase('User') }}</label>
                                            <select class="form-control px-14px ol-form-control ol-select2" data-toggle="select2" name="user_id" id="select_user_id" required>
                                                <option value="">{{ get_phrase('Select a user') }}</option>
                                                @php
                                                    $all_users = App\Models\User::whereNot('role', 'admin')->get();
                                                @endphp
                                                @foreach ($all_users as $all_user)
                                                    <option value="{{ $all_user->id }}" {{ $ticket_details->user_id == $all_user->id ? 'selected' : '' }}>
                                                        {{ $all_user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="fpb7 mb-2">
                                            <label class="form-label ol-form-label" for="status_id">{{ get_phrase('Status') }}</label>
                                            <select class="form-control px-14px ol-form-control ol-select2" data-toggle="select2" name="status_id" required>
                                                <option value=""> {{ get_phrase('Select a status') }} </option>
                                                @php
                                                    $all_statuses = App\Models\TicketStatus::where('status', 1)->get();
                                                @endphp
                                                @foreach ($all_statuses as $all_status)
                                                    <option value="{{ $all_status->id }}" {{ $ticket_details->status_id == $all_status->id ? 'selected' : '' }}>
                                                        {{ $all_status->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="fpb7 mb-2">
                                            <label class="form-label ol-form-label" for="priority_id">{{ get_phrase('Priority') }}</label>
                                            <select class="form-control px-14px ol-form-control ol-select2" data-toggle="select2" name="priority_id" required>
                                                <option value=""> {{ get_phrase('Select a priority') }} </option>
                                                @php
                                                    $all_priorities = App\Models\TicketPriority::where('status', 1)->get();
                                                @endphp
                                                @foreach ($all_priorities as $all_priority)
                                                    <option value="{{ $all_priority->id }}" {{ $ticket_details->priority_id == $all_priority->id ? 'selected' : '' }}>
                                                        {{ $all_priority->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="fpb7 mb-2">
                                            <label class="form-label ol-form-label" for="category_id">{{ get_phrase('Category') }}</label>
                                            <select class="form-control px-14px ol-form-control ol-select2" data-toggle="select2" name="category_id" required>
                                                <option value=""> {{ get_phrase('Select a category') }} </option>
                                                @php
                                                    $all_categories = App\Models\TicketCategory::where('status', 1)->get();
                                                @endphp
                                                @foreach ($all_categories as $all_category)
                                                    <option value="{{ $all_category->id }}" {{ $ticket_details->category_id == $all_category->id ? 'selected' : '' }}>
                                                        {{ $all_category->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <ul class="messenger-body" id="scrollAbleContent">
                        @php
                            $my_data = auth()->user();
                        @endphp
                        @if (count($messages) == 0)
                            <div class="no-message card-centered-section">
                                <div class="card-middle-banner">
                                    <img src="{{ get_image('assets/images/icons/no-message.svg') }}" alt="">
                                </div>
                            </div>
                        @else
                            @foreach ($messages as $message)
                                @php
                                    $user_details = App\Models\User::where('id', $message->sender_id)->first();
                                @endphp
                                @if ($message->sender_id == $my_data->id || $user_details?->role == 'admin')
                                    <li>
                                        <div class="row">
                                            <div class="col--md-12 d-flex align-items-start">
                                                <!-- Profile Picture -->
                                                <img src="{{ get_image($user_details->photo) }}" class="rounded-circle me-3" width="50" height="50">

                                                <!-- Comment Content -->
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center">
                                                        <h4 class="message-name">{{ $user_details->name }}</h4>
                                                        <span class="text-grey ms-2">•
                                                            {{ \Carbon\Carbon::parse($message->created_at)->format('M j, g:i A') }}
                                                        </span>
                                                    </div>
                                                    <div class="comment-box mt-2 gallery">
                                                        <p class="text-grey message-text" data-original-text="{{ $message->message }}">
                                                            {!! nl2br(e($message->message)) !!}
                                                        </p>
                                                        @if (!empty($message->file))
                                                            @php
                                                                $files = json_decode($message->file, true);
                                                            @endphp
                                                            @if (!empty($files) || is_array($files))
                                                                @foreach ($files as $file)
                                                                    @php
                                                                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                                                                        $fileUrl = get_image($file);
                                                                    @endphp
                                                                    @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'pdf']))
                                                                        <div class="file-preview-wrapper d-inline-block me-2 mb-2">
                                                                            <a href="javascript:void(0);" class="file-link" data-type="{{ strtolower($ext) }}" data-url="{{ $fileUrl }}" data-bs-toggle="modal" data-bs-target="#filePreviewModal">

                                                                                @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                                    <img src="{{ $fileUrl }}" class="rounded-3 comment-img mt-3" width="300" alt="Uploaded Image">
                                                                                @else
                                                                                    <div class="rounded-3 comment-img mt-3 d-flex align-items-center justify-content-center bg-light border" style="width:300px; height:200px;">
                                                                                        <p class="text-center text-muted mb-0">{{ strtoupper($ext) }} File</p>
                                                                                    </div>
                                                                                @endif
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <p>{{ get_phrase('No files available') }}</p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- More Options -->
                                                <div class="ms-auto">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @else
                                    <li>
                                        <div class="col-md-12 d-flex align-items-start">
                                            <!-- Profile Picture -->
                                            <img src="{{ get_image($user_details->photo) }}" class="rounded-circle me-3" width="50" height="50">


                                            <!-- Comment Content -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <h4 class="message-name">{{ $user_details->name }}</h4>
                                                    <span class="text-grey ms-2">•
                                                        {{ \Carbon\Carbon::parse($message->created_at)->format('M j, g:i A') }}
                                                    </span>
                                                </div>
                                                <div class="comment-box mt-2 gallery">
                                                    <p class="text-grey message-text" data-original-text="{{ $message->message }}">
                                                        {!! nl2br(e($message->message)) !!}
                                                    </p>
                                                    @if (!empty($message->file))
                                                        @php
                                                            $files = json_decode($message->file, true);
                                                        @endphp
                                                        @if (!empty($files) || is_array($files))
                                                            @foreach ($files as $file)
                                                                @php
                                                                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                                                                    $fileUrl = get_image($file);
                                                                @endphp
                                                                @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'pdf']))
                                                                    <div class="file-preview-wrapper d-inline-block me-2 mb-2">
                                                                        <a href="javascript:void(0);" class="file-link" data-type="{{ strtolower($ext) }}" data-url="{{ $fileUrl }}" data-bs-toggle="modal" data-bs-target="#filePreviewModal">

                                                                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                                <img src="{{ $fileUrl }}" class="rounded-3 comment-img mt-3" width="300" alt="Uploaded Image">
                                                                            @else
                                                                                <div class="rounded-3 comment-img mt-3 d-flex align-items-center justify-content-center bg-light border" style="width:300px; height:200px;">
                                                                                    <p class="text-center text-muted mb-0">{{ strtoupper($ext) }} File</p>
                                                                                </div>
                                                                            @endif
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <p>{{ get_phrase('No files available') }}</p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- More Options -->
                                            <div class="ms-auto">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>

                    <div class="div-2"></div>

                    <div class="messenger-footer upload-container">

                        <input type="hidden" name="sender_id" value="{{ $my_data->id }}">
                        <input type="hidden" name="receiver_id" value="{{ $ticket_details->creator_id }}">
                        <input type="hidden" name="ticket_thread_code" value="{{ $ticket_details->code }}">

                        <div class="messenger-footer-inner d-flex align-items-end">
                            <textarea class="form-control form-control-message" id="messageInput" name="message" rows="6" cols="50" placeholder="Type your message here..."></textarea>

                            <div class="drop-zone cursor-pointer" id="dropZone">
                                <span>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 23.75C8.27734 23.75 5.25 20.7227 5.25 17V5C5.25 2.38086 7.38086 0.25 10 0.25C12.6191 0.25 14.75 2.38086 14.75 5V16C14.75 17.5156 13.5156 18.75 12 18.75C10.4844 18.75 9.25 17.5156 9.25 16V7C9.25 6.58594 9.58594 6.25 10 6.25C10.4141 6.25 10.75 6.58594 10.75 7V16C10.75 16.6894 11.3105 17.25 12 17.25C12.6895 17.25 13.25 16.6894 13.25 16V5C13.25 3.20801 11.793 1.75 10 1.75C8.20703 1.75 6.75 3.20801 6.75 5V17C6.75 19.8945 9.10547 22.25 12 22.25C14.8945 22.25 17.25 19.8945 17.25 17V7C17.25 6.58594 17.5859 6.25 18 6.25C18.4141 6.25 18.75 6.58594 18.75 7V17C18.75 20.7227 15.7227 23.75 12 23.75Z"
                                            fill="#6D718C" />
                                    </svg>
                                </span>
                                <input type="file" id="formFile" name="file[]" multiple hidden>
                            </div>

                            <button type="submit" id="send-button" class="btn ol-btn-primary d-flex align-items-center cg-6px">
                                <span>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.16641 5.26655L13.2414 2.90822C16.4164 1.84988 18.1414 3.58322 17.0914 6.75822L14.7331 13.8332C13.1497 18.5916 10.5497 18.5916 8.96641 13.8332L8.26641 11.7332L6.16641 11.0332C1.40807 9.44988 1.40807 6.85822 6.16641 5.26655Z" stroke="white"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8.42188 11.375L11.4052 8.3833" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                </span>
                                <span>{{ get_phrase('Send') }}</span>
                            </button>

                        </div>
                        <ul class="file-list" id="fileList"></ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- End Admin area -->

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body custome-modal text-center" id="fileContent">
                    <!-- File content loads here dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_phrase('Close') }}</button>
                    <button type="button" id="downloadFile" class="btn btn-primary">{{ get_phrase('Download') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')

    <script>
        $(document).ready(function() {
            let currentFileUrl = '';

            // When user clicks on a file preview
            $('.file-link').on('click', function() {
                const fileUrl = $(this).data('url');
                const fileType = $(this).data('type');
                currentFileUrl = fileUrl;

                let content = '';

                // Detect file type
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileType)) {
                    content = `<img src="${fileUrl}" class="img-fluid rounded" style="max-height:80vh;">`;
                } else if (['mp4', 'webm', 'ogg'].includes(fileType)) {
                    content = `<video controls class="img-fluid rounded" style="max-height:80vh;">
                           <source src="${fileUrl}" type="video/${fileType}">
                           Your browser does not support the video tag.
                       </video>`;
                } else if (fileType === 'pdf') {
                    content = `<iframe src="${fileUrl}" style="width:100%; height:80vh;" frameborder="0"></iframe>`;
                } else {
                    content = `<p>Preview not available. Please download the file.</p>`;
                }

                $('#fileContent').html(content);
            });

            // Download button works properly
            $('#downloadFile').on('click', function() {
                if (!currentFileUrl) return;

                const link = document.createElement('a');
                link.href = currentFileUrl;

                // Extract file name safely
                const filename = currentFileUrl.split('/').pop().split('?')[0];
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#macroSelect').on('change', function() {
                let selectedText = $(this).val();
                let messageInput = document.getElementById('messageInput');

                if (selectedText && messageInput) {
                    let startPos = messageInput.selectionStart;
                    let endPos = messageInput.selectionEnd;
                    let text = messageInput.value;

                    messageInput.value = text.substring(0, startPos) + selectedText + text.substring(endPos);

                    messageInput.selectionStart = messageInput.selectionEnd = startPos + selectedText.length;
                    messageInput.focus();

                    setTimeout(() => {
                        $(this).val('').trigger('change'); // reset value in select2
                    }, 300);
                }
            });
        });
    </script>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('formFile');
        const fileList = document.getElementById('fileList');
        let filesArray = []; // Store selected files

        // Trigger file dialog
        dropZone.addEventListener('click', () => fileInput.click());

        // Handle file selection
        fileInput.addEventListener('change', (e) => {
            filesArray = Array.from(fileInput.files);
            updateFileList();
        });

        // Drag & drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '#f1f1f1';
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.style.backgroundColor = '';
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
            filesArray = Array.from(e.dataTransfer.files);
            updateFileList();
        });

        // Update the displayed file list
        function updateFileList() {
            fileList.innerHTML = '';
            filesArray.forEach((file, index) => {
                const li = document.createElement('li');
                li.style.display = 'flex';
                li.style.alignItems = 'center';
                li.style.justifyContent = 'space-between';
                li.style.marginBottom = '5px';
                li.style.padding = '6px 10px';
                li.style.background = '#f5f5f5';
                li.style.borderRadius = '6px';

                li.innerHTML = `
            <span>${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>
            <button type="button" class="remove-btn" data-index="${index}" style="
                background: transparent;
                border: none;
                cursor: pointer;
                font-size: 16px;
                color: #ff5c5c;
            ">&times;</button>
        `;

                fileList.appendChild(li);
            });

            // Update hidden input's FileList using DataTransfer
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;

            // Attach remove button listeners
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const i = e.target.dataset.index;
                    filesArray.splice(i, 1);
                    updateFileList();
                });
            });
        }
    </script>

@endpush
