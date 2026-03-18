<form id="custom_form" method="post" enctype="multipart/form-data" action="{{ route('admin.custom.fields.update', ['field_id' => $customField, 'item_id' => $item['id']]) }}">

    @csrf
    <input type="hidden" name="item_id" value="{{ $customField['id'] }}">

    <div class="row">
        <div class="col-sm-12">
            <div class="mb-3">
                <div class="alert alert-info" role="alert">
                    {{ get_phrase('Custom Field Type : ') }}<strong class="capitalize"> {{ $customField['custom_type'] }}</strong>
                </div>
            </div>
        </div>

        {{-- IMAGE FIELDS --}}
        @if ($customField['custom_type'] == 'image')
            <div class="col-sm-12 custom-field-group" id="image_fields">
                <div id="image_field_container">
                    <div class="image-field-repeat rounded border p-3 mb-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label ol-form-label">{{ get_phrase('Title') }}</label>
                            </div>
                            <input type="text" name="image_title[]" value="{{ $item['title'] ?? '' }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label ol-form-label">{{ get_phrase('Description') }}</label>
                            <textarea name="image_description[]" class="form-control" style="height: 150px;">{{ $item['description'] ?? '' }}</textarea>
                        </div>

                        <div class="mb-3">
                            @if (!empty($item['file']))
                                <div class="form-group">
                                    <img src="{{ asset('uploads/custom-fields/' . $item['file']) }}" width="200" style="height: 120px; border-radius: 5px; object-fit: cover;">
                                </div>
                            @endif
                            <label class="form-label ol-form-label mt-2">{{ get_phrase('Image') }}</label>
                            <input type="file" name="image_file[]" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        @elseif($customField['custom_type'] == 'text')
            {{-- TEXT FIELDS --}}
            <div class="col-sm-12 custom-field-group" id="text_fields">
                <div id="text_field_container">
                    <div class="text-field-repeat rounded border p-3 mb-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label ol-form-label">{{ get_phrase('Text Content') }}</label>
                            </div>
                            <textarea id="summernote" name="text_content[]" class="form-control" style="height: 150px;">{!! $item['content'] ?? '' !!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($customField['custom_type'] == 'slider')
            {{-- SLIDER FIELDS --}}
            <div class="col-sm-12 custom-field-group">
                <div id="slider_field_container">
                    <div class="slider-field-repeat rounded border p-3 mb-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label ol-form-label">{{ get_phrase('Title') }}</label>
                            </div>
                            <input type="text" name="slider_title[]" class="form-control" value="{{ $item['title'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label ol-form-label">{{ get_phrase('Description') }}</label>
                            <textarea name="slider_description[]" class="form-control" style="height: 150px;">{{ $item['description'] ?? '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            @if (!empty($item['file']))
                                <div class="form-group">
                                    <img src="{{ asset('uploads/custom-fields/' . $item['file']) }}" width="200" style="height: 120px; border-radius: 5px; object-fit: cover;">
                                </div>
                            @endif
                            <label class="form-label ol-form-label mt-2">{{ get_phrase('Image') }}</label>
                            <input type="file" name="slider_images[]" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        @elseif($customField['custom_type'] == 'video')
            {{-- VIDEO FIELDS --}}
            <div class="col-sm-12 custom-field-group">
                <div id="video_field_container">
                    <div class="video-field-repeat rounded border p-3 mb-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label ol-form-label">{{ get_phrase('Video URL') }}</label>
                            </div>
                            <input type="text" name="video_url[]" class="form-control" value="{{ $item['url'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        @elseif($customField['custom_type'] == 'faq')
            {{-- FAQ FIELDS --}}
            <div class="col-sm-12 custom-field-group" id="faq_fields">
                <div id="faq_field_container">
                    <div class="faq-field-repeat rounded border p-3 mb-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label ol-form-label">{{ get_phrase('FAQ Question') }}</label>
                            </div>
                            <input type="text" name="faq_question[]" value="{{ $item['question'] ?? '' }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label ol-form-label">{{ get_phrase('FAQ Answer') }}</label>
                            <textarea name="faq_answer[]" class="form-control" style="height: 150px;">{{ $item['answer'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($customField['custom_type'] == 'gallery')
            {{-- Gallery   FIELDS --}}
            <div class="col-sm-12 custom-field-group" id="gallery_fields">
                <div id="gallery_field_container">
                    <div class="gallery-field-repeat rounded border p-3 mb-3">
                        <div class="mb-3">
                            @if (!empty($item['file']))
                                <div class="form-group">
                                    <img src="{{ asset('uploads/custom-fields/' . $item['file']) }}" width="200" style="height: 120px; border-radius: 5px; object-fit: cover;">
                                </div>
                            @endif
                            <label class="form-label ol-form-label">{{ get_phrase('Gallery Image') }}</label>
                            <input type="file" name="gallery_images[]" multiple class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        @endif



        {{-- SUBMIT BUTTON --}}
        <div class="col-sm-12 text-end mt-3" id="submit_button_wrapper">
            <button type="submit" class="btn ol-btn-primary ">{{ get_phrase('Update') }}</button>
        </div>

    </div>
</form>
<script type="text/javascript">
    "use strict";

    $('#summernote').summernote({
        tabsize: 2,
        height: 120,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']]
        ]
    });
</script>
