<li class="message message-for-me d-flex justify-content-start align-items-start g-14 flex-row-reverse">
    <div class="message-text">
        <ul class="message-list">
            <li>
                @if (!empty($message->message))
                    <p>{{ $message->message }}</p>
                @endif

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
                                <div class="message-upload-images d-flex flex-wrap g-20 justify-content-end">
                                    <a href="javascript:void(0);" class="file-link" data-type="{{ strtolower($ext) }}" data-url="{{ $fileUrl }}" data-bs-toggle="modal" data-bs-target="#filePreviewModal">

                                        @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <img src="{{ $fileUrl }}" class="rounded-3 comment-img mt-3" width="200" alt="Uploaded Image">
                                        @else
                                            <div class="rounded-3 comment-img mt-3 d-flex align-items-center justify-content-center bg-light border" style="width:200px; height:140px;">
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

            </li>
        </ul>
        <div class="message-user d-flex align-items-center g-20 flex-row-reverse pb-17">
            <p class="fz-13-m-grayish">{{ timeAgo($message->created_at) }}</p>
        </div>
    </div>
</li>
