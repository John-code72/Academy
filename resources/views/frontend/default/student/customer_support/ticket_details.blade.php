@extends('layouts.default')
@push('title', get_phrase('Customer Support'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <style>
        .message-input .form-control.message-box {
            height: 50px;
        }

        .custome-modal img {
            max-width: 100%;
            width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center;
            margin: 0 auto;
        }
    </style>
    <section class="wishlist-content">
        <div class="profile-banner-area"></div>
        <div class="container profile-banner-area-container">
            <div class="row">
                @include('frontend.default.student.left_sidebar')

                <div class="col-lg-9">
                    <h4 class="g-title mb-5">{{ get_phrase('Ticket Subject') }} : {{ $ticket_details->subject }}</h4>

                    <div class="my-panel message-panel">
                        <div class="row">

                            <div class="col-lg-12">
                                <div class="message-right position-relative">
                                    <div class="count-files d-flex align-items-center gap-3 cursor-pointer d-none">
                                        <p></p>
                                        <i class="fa-regular fa-circle-xmark" id="remove_files"></i>
                                    </div>
                                    <div class="tab-content" id="v-pills-tabContent">
                                        <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab" tabindex="0">
                                            <div class="message-header d-flex justify-content-between align-items-center pb-20 ">
                                                <div class="ins-nav w-100 align-items-center">
                                                    <div class="ins-left flex-wrap">
                                                        <h4> <span class="g-title fs-4">{{ get_phrase('Status') }} : </span> {{ $ticket_details->status->title }}</h4> |
                                                        <h4> <span class="g-title fs-4">{{ get_phrase('Priority') }} : </span> {{ $ticket_details->priority->title }}</h4> |
                                                        <h4> <span class="g-title fs-4">{{ get_phrase('Category') }} : </span> {{ $ticket_details->category->title }}</h4>
                                                    </div>
                                                    <a href="{{ route('support.ticket.index') }}" class="eBtn gradient  d-flex align-items-center text-nowrap"><i class="fi-rr-arrow-alt-left me-2"></i> {{ get_phrase('Back') }}</a>

                                                </div>
                                            </div>


                                            <div class="custome-height" id="msg-box">
                                                @foreach ($conversation as $message)
                                                    @if ($message->sender_id == auth()->user()->id)
                                                        @include('frontend.default.student.customer_support.sent')
                                                    @else
                                                        @include('frontend.default.student.customer_support.received')
                                                    @endif
                                                @endforeach
                                            </div>

                                            <div class="message-send-option d-flex align-items-end g-12 pt-40">
                                                <div class="message-input d-flex justify-content-start align-items-center">
                                                    <form action="{{ route('support.ticket.message.store') }}" method="post" class="w-100 align-items-center" enctype="multipart/form-data">
                                                        @csrf
                                                        <div>
                                                            <input type="hidden" name="sender_id" value="{{ auth()->user()->id }}">
                                                            <input type="hidden" name="receiver_id" value="{{ $ticket_details->creator_id }}">
                                                            <input type="hidden" name="ticket_thread_code" value="{{ $ticket_details->code }}">

                                                            <textarea class="form-control message-box" id="type-msg" name="message" placeholder="Type your message here..."></textarea>
                                                        </div>

                                                        <ul class="ic-control d-flex ">
                                                            <li>
                                                                <label for="gallery"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M7.49935 18.3334H12.4993C16.666 18.3334 18.3327 16.6667 18.3327 12.5001V7.50008C18.3327 3.33341 16.666 1.66675 12.4993 1.66675H7.49935C3.33268 1.66675 1.66602 3.33341 1.66602 7.50008V12.5001C1.66602 16.6667 3.33268 18.3334 7.49935 18.3334Z"
                                                                            stroke="#6B7385" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
                                                                        </path>
                                                                        <path d="M7.50065 8.33333C8.42113 8.33333 9.16732 7.58714 9.16732 6.66667C9.16732 5.74619 8.42113 5 7.50065 5C6.58018 5 5.83398 5.74619 5.83398 6.66667C5.83398 7.58714 6.58018 8.33333 7.50065 8.33333Z" stroke="#6B7385"
                                                                            stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
                                                                        </path>
                                                                        <path
                                                                            d="M2.22461 15.7916L6.33294 13.0332C6.99128 12.5916 7.94128 12.6416 8.53294 13.1499L8.80794 13.3916C9.45794 13.9499 10.5079 13.9499 11.1579 13.3916L14.6246 10.4166C15.2746 9.85822 16.3246 9.85822 16.9746 10.4166L18.3329 11.5832"
                                                                            stroke="#6B7385" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
                                                                        </path>
                                                                    </svg>
                                                                </label>
                                                                <input type="file" name="file[]" id="gallery" class="d-none" multiple="multiple">
                                                            </li>
                                                        </ul>
                                                    </form>
                                                </div>
                                                <button class="send_message_btn">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M7.39969 6.31991L15.8897 3.48991C19.6997 2.21991 21.7697 4.29991 20.5097 8.10991L17.6797 16.5999C15.7797 22.3099 12.6597 22.3099 10.7597 16.5999L9.91969 14.0799L7.39969 13.2399C1.68969 11.3399 1.68969 8.22991 7.39969 6.31991Z"
                                                            stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        </path>
                                                        <path d="M10.1094 13.6501L13.6894 10.0601" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!------------ Ticket area End  ------------>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body custome-modal text-center" id="fileContent">
                    <!-- File content loads here dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="eBtn gradient" data-bs-dismiss="modal">{{ get_phrase('Close') }}</button>
                    <button type="button" id="downloadFile" class="eBtn gradient">{{ get_phrase('Download') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')

    <script>
        "use strict";
        $(document).ready(function() {
            $('.send_message_btn').on('click', function(e) {
                let msg = $('#type-msg').val();
                $('.message-input form').trigger('submit');
            });

            $('#gallery').change(function(e) {
                e.preventDefault();
                var fileCount = $(this)[0].files.length;
                if (fileCount > 0) {
                    $('.count-files').removeClass('d-none');
                    $('.count-files p').text(fileCount + ' files selected');
                    $(this).attr('name', 'file[]');
                }
            });

            $('#remove_files').on('click', function(e) {
                $('.count-files').addClass('d-none');
                $('.message-input #gallery').removeAttr('name');
            });
        });
    </script>
    <script>
        "use strict";
        var myDiv = document.getElementById('msg-box');
        myDiv.scrollTop = myDiv.scrollHeight;
    </script>
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

@endpush
