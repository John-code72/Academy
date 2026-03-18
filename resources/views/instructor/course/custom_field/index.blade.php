<style>
    .nav-link {
        text-align: left;
    }

    .carousel-item img {
        height: 400px;
        object-fit: cover;
        border-radius: 10px;
    }

    .ratio {
        height: 430px;
    }

    .list-group-item {
        border: 1px solid #e1dede !important;
        border-radius: 5px;
    }

    .card-text {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .eCard .card-body {
        padding: 11px 14px;
    }

    .eCard .card-title {
        font-size: 16px !important;
        font-weight: 600 !important;
    }

    .bg-card {
        box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
        border-color: #e7e7e7;
        background: #fff;
    }

    .ratio {
        border-radius: 10px;
    }

    .eControll {
        top: 8px;
        right: auto;
        left: 6px;
        width: 20px;
    }

    .eControll i {
        font-size: 12px;
        /* border: 1px solid var(--skinColor); */
        color: var(--skinColor);
        height: 26px;
        width: 26px;
        line-height: 24px;
        text-align: center;
        border-radius: 50%;
        transition: .5s;

    }



    .eControll i {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .eControll .fa-edit {
        background: #1982FE;
        color: #fff;
    }

    .eControll .fa-trash {
        background: #ff4625;
        color: #fff;
    }

    .list-group-item p {
        font-size: 14px;
    }

    .dragable-item {
        background: #dfedffb5;
        color: #010101;
        padding: 14px 12px;
        border-radius: 5px;
        margin-bottom: 10px;
        cursor: move;
    }

    .ui-sortable-placeholder {
        background: #e0e0e0;
        border: 2px dashed #ccc;
        height: 40px;
    }

    .notes {
        font-size: 12px;
        color: #010101;
        background: #797c8b30;
        border-radius: 3px;
        padding: 7px 13px;
    }

    .singleFaq {
        border-bottom: 1px solid #dbd5d5;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .singleFaq h4 {
        font-size: 17px;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .singleFaq p {
        font-size: 14px;
        line-height: 24px;
    }

    .accor_wrap {}

    .accor_wrap ul {
        position: absolute;
        right: 50px;
        visibility: hidden;
        opacity: 0;
        transition: .3s;
        z-index: 999;
    }

    .accor_wrap li a {
        height: 30px;
        width: 30px;
        border-radius: 4px;
        border: 1px solid #e0e5f3;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: .3s;

    }

    .accor_wrap li a i {
        font-size: 15px;
    }

    .accor_wrap li a:hover {
        color: #1982FE;
        border-color: #1982FE;
    }

    .accor_wrap:hover ul {
        visibility: visible;
        opacity: 1;
    }

    .accordion-item {
        margin-bottom: 10px;
        border: none;

    }

    .accor_wrap {
        position: relative;
    }

    .accor_wrap .accordion-button {
        padding: 14px 17px;
        border: 1px solid #e5e1e1 !important;
        border-radius: 5px;
    }

    .accordion-button:not(.collapsed) {
        box-shadow: none;
    }

    .accordion-button {
        font-weight: 500;
        font-size: 14px;
        line-height: 20px;
        color: var(--darkColor);
    }

    .accordion-button::after {
        background-image: var(--bs-accordion-btn-icon) !important;
        z-index: 999;
    }

    .accordion-button:not(.collapsed)::after {
        transform: inherit;
    }

    .accordion-body {
        border: 1px solid #e5e1e1 !important;
        border-top: 0 !important;
        border-radius: 0 0 5px 5px;
    }

    .accordion-button:not(.collapsed) {
        color: var(--darkColor);
    }

    .carousel-caption h5 {
        font-size: 19px;
        margin-bottom: 13px;
    }

    .eControll {
        position: absolute;
        top: 8px;
        right: 7px;
        z-index: 999;
        color: #ff4625;
        visibility: hidden;
        opacity: 0;
        transition: .5s;
    }

    .eCard:hover .eControll {
        visibility: visible;
        opacity: 1;
    }

    .custom-accordion .accordion-button:focus {

        box-shadow: none;



    }
</style>
@php
    $customFields = App\Models\CustomField::where('course_id', $course_details->id)->orderBy('sorting', 'asc')->get();
    $customTitles = $customFields->pluck('custom_title', 'custom_type')->toArray();
@endphp
<div class="row">
    <div class="col-lg-12">
        <div class="accordion custom-accordion" id="accordionExample">

            {{-- IMAGE SECTION --}}
            @foreach ($customFields as $field)
                @if ($field->custom_type == 'image')
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <div class="accor_wrap d-flex justify-content-between align-items-center">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImage" aria-expanded="true">
                                    {{ $customTitles['image'] ?? '' }}
                                </button>
                                <ul class="d-flex gap-2">
                                    <li>
                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.section.edit', ['id' => $field->id]) }}','{{ get_phrase('Edit Section') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.section.delete', ['id' => $field->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </h2>
                        <div id="collapseImage" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row">
                                    @foreach (json_decode($field->custom_field)->data as $image)
                                        <div class="col-lg-6 mb-3">
                                            <div class="card eCard bg-card position-relative">
                                                <div class="row g-0">
                                                    <div class="col-md-4">
                                                        <img src="{{ asset('uploads/custom-fields/' . $image->file) }}" class="img-fluid rounded-start w-100" style="height: 150px; object-fit: cover;">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card-body">
                                                            <h5 class="card-title">{{ $image->title }}</h5>
                                                            <p class="card-text">{{ $image->description }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="eControll d-flex gap-2 position-absolute top-0 end-0 p-2">
                                                    <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.fields.edit', ['field_id' => $field->id, 'item_id' => $image->id]) }}','{{ get_phrase('Edit Field') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.fields.delete', ['field_id' => $field->id, 'item_id' => $image->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($field->custom_type == 'slider')
                    {{-- SLIDER SECTION --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <div class="accor_wrap d-flex justify-content-between align-items-center">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSlider{{ $field->id }}">
                                    {{ $customTitles['slider'] ?? '' }}
                                </button>
                                <ul class="d-flex gap-2">
                                    <li>
                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.section.edit', ['id' => $field->id]) }}','{{ get_phrase('Edit Section') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.section.delete', ['id' => $field->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </h2>
                        <div id="collapseSlider{{ $field->id }}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div id="carousel{{ $field->id }}" class="carousel slide">
                                    <div class="carousel-inner eCard">
                                        @php $first = true; @endphp
                                        @foreach (json_decode($field->custom_field)->data as $slide)
                                            <div class="carousel-item {{ $first ? 'active' : '' }}">
                                                <img src="{{ asset('uploads/custom-fields/' . $slide->file) }}" class="d-block w-100" alt="">
                                                <div class="carousel-caption d-none d-md-block">
                                                    <h5>{{ $slide->title }}</h5>
                                                    <p>{{ $slide->description }}</p>
                                                </div>
                                                <div class="eControll d-flex gap-2">
                                                    <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.fields.edit', ['field_id' => $field->id, 'item_id' => $slide->id]) }}','{{ get_phrase('Edit Field') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.fields.delete', ['field_id' => $field->id, 'item_id' => $slide->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            @php $first = false; @endphp
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $field->id }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $field->id }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($field->custom_type == 'text')
                    {{-- TEXT SECTION --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <div class="accor_wrap d-flex justify-content-between align-items-center">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseText{{ $field->id }}">
                                    {{ $customTitles['text'] ?? '' }}
                                </button>
                                <ul class="d-flex gap-2">
                                    <li>
                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.section.edit', ['id' => $field->id]) }}','{{ get_phrase('Edit Section') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.section.delete', ['id' => $field->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </h2>
                        <div id="collapseText{{ $field->id }}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ul class="list-group gap-3">
                                    @foreach (json_decode($field->custom_field)->data as $text)
                                        <li class="list-group-item eCard">
                                            <p>{!! $text->content !!}</p>
                                            <div class="eControll d-flex gap-2">
                                                <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.fields.edit', ['field_id' => $field->id, 'item_id' => $text->id]) }}','{{ get_phrase('Edit Field') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                                    <i class="far fa-edit"></i>
                                                </a>
                                                <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.fields.delete', ['field_id' => $field->id, 'item_id' => $text->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif($field->custom_type == 'video')
                    {{-- VIDEO SECTION --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <div class="accor_wrap d-flex justify-content-between align-items-center">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVideo{{ $field->id }}">
                                    {{ $customTitles['video'] ?? '' }}
                                </button>
                                <ul class="d-flex gap-2">
                                    <li>
                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.section.edit', ['id' => $field->id]) }}','{{ get_phrase('Edit Section') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.section.delete', ['id' => $field->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </h2>
                        <div id="collapseVideo{{ $field->id }}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row">
                                    @foreach (json_decode($field->custom_field)->data as $video)
                                        @php
                                            $url = $video->url;
                                            $parsed_url = parse_url($url);
                                            $embed_url = null;

                                            if (isset($parsed_url['host'])) {
                                                if (strpos($parsed_url['host'], 'youtube.com') !== false) {
                                                    parse_str($parsed_url['query'], $params);
                                                    $video_id = $params['v'] ?? '';
                                                    $embed_url = "https://www.youtube.com/embed/{$video_id}";
                                                } elseif (strpos($parsed_url['host'], 'youtu.be') !== false) {
                                                    $video_id = ltrim($parsed_url['path'], '/');
                                                    $embed_url = "https://www.youtube.com/embed/{$video_id}";
                                                }
                                            }
                                        @endphp

                                        @if ($embed_url)
                                            <div class="col-lg-12 mb-3">
                                                <div class="ratio ratio-16x9 eCard">
                                                    <iframe src="{{ $embed_url }}" allowfullscreen></iframe>
                                                    <div class="eControll d-flex gap-2">
                                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.fields.edit', ['field_id' => $field->id, 'item_id' => $video->id]) }}','{{ get_phrase('Edit Field') }}')" data-bs-toggle="tooltip"
                                                            data-bs-title="{{ get_phrase('Edit') }}">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.fields.delete', ['field_id' => $field->id, 'item_id' => $video->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($field->custom_type == 'faq')
                    {{-- FAQ  SECTION --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <div class="accor_wrap d-flex justify-content-between align-items-center">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsefaq" aria-expanded="true" aria-controls="collapsefaq">
                                    {{ $customTitles['faq'] ?? '' }}
                                </button>
                                <ul class="d-flex gap-2">
                                    <li>
                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.section.edit', ['id' => $field->id]) }}','{{ get_phrase('Edit Section') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.section.delete', ['id' => $field->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </h2>
                        <div id="collapsefaq" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="faq-figure row">
                                    @foreach ($customFields->where('custom_type', 'faq') as $field)
                                        @foreach (json_decode($field->custom_field)->data as $index => $faq)
                                            <div class="col-lg-6">
                                                <ul class=" eCard">
                                                    <li class="singleFaq">
                                                        <h4>{{ $faq->question }}</h4>
                                                        <p> {{ $faq->answer }}</p>
                                                        <div class="eControll  d-flex gap-2">
                                                            <a href="javascripti:;" onclick="ajaxModal('{{ route('instructor.custom.fields.edit', ['field_id' => $field->id, 'item_id' => $faq->id]) }}','{{ get_phrase('Edit Field') }}')" data-bs-toggle="tooltip"
                                                                data-bs-title="{{ get_phrase('Edit') }}"><i class="far fa-edit"></i></a>
                                                            <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.fields.delete', ['field_id' => $field->id, 'item_id' => $faq->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($field->custom_type == 'gallery')
                    {{-- GALLERY SECTION  --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <div class="accor_wrap d-flex justify-content-between align-items-center">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsegallery" aria-expanded="true" aria-controls="collapsegallery">
                                    {{ $customTitles['gallery'] ?? '' }}
                                </button>
                                <ul class="d-flex gap-2">
                                    <li>
                                        <a href="javascript:;" onclick="ajaxModal('{{ route('instructor.custom.section.edit', ['id' => $field->id]) }}','{{ get_phrase('Edit Section') }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Edit') }}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.section.delete', ['id' => $field->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </h2>
                        <div id="collapsegallery" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="row">
                                    @foreach ($customFields->where('custom_type', 'gallery') as $field)
                                        @foreach (json_decode($field->custom_field)->data as $image)
                                            <div class="col-lg-3">
                                                <div class="card eCard mb-3 position-relative">
                                                    <img src="{{ asset('uploads/custom-fields/' . $image->file) }}" class="img-fluid rounded-start w-100" style="height: 260px; object-fit: cover; border-radius: 5px;" alt="">
                                                    <div class="eControll  d-flex gap-2 position-absolute top-0 end-0 p-2">
                                                        <a href="javascripti:;" onclick="ajaxModal('{{ route('instructor.custom.fields.edit', ['field_id' => $field->id, 'item_id' => $image->id]) }}','{{ get_phrase('Edit Field') }}')" data-bs-toggle="tooltip"
                                                            data-bs-title="{{ get_phrase('Edit') }}"><i class="far fa-edit"></i></a>
                                                        <a href="javascript:;" onclick="confirmModal('{{ route('instructor.custom.fields.delete', ['field_id' => $field->id, 'item_id' => $image->id]) }}')" data-bs-toggle="tooltip" data-bs-title="{{ get_phrase('Delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            let target = $(e.target).attr('data-bs-target');
            localStorage.setItem('activeTab', target);
        });
        let activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            let tabTrigger = document.querySelector(`button[data-bs-target="${activeTab}"]`);
            if (tabTrigger) {
                new bootstrap.Tab(tabTrigger).show();
            }
        }
    });
</script>
