<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fs-16px title mb-3 capitalize"> {{ get_phrase('Custom Field') }} </h5>
    <div>
        @php
            $getSorting = App\Models\CustomField::where('course_id', $course_details->id)->get();
        @endphp
        @if (count($getSorting) > 0)
            <a href="javascript:void(0);" onclick="ajaxModal('{{ route('instructor.section.sorting', $course_details->id) }}', '{{ get_phrase(' Sort Section') }}')" class="btn ol-btn-primary fs-14px"> {{ get_phrase('Sorting') }} </a>
        @endif
        <a href="javascript:void(0);" onclick="ajaxModal('{{ route('instructor.custom.fields.create', $course_details->id) }}', '{{ get_phrase('Add Custom Field') }}')" class="btn ol-btn-primary"> {{ get_phrase('Add Type') }} </a>

    </div>
</div>
@include('instructor.course.custom_field.index')
