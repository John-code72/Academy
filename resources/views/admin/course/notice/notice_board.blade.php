<style>
    .notice-card {
        background-color: #e8f8f5;
        border-radius: 10px;
    }

    .notice-item {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .notice-header {
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .notice-icon {
        font-size: 1rem;
        color: #1abc9c;
        flex-shrink: 0;
    }

    .notice-title {
        color: #1abc9c;
        font-size: 14px;
        font-weight: 600;
        margin: 0;
    }

    .notice-desc {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notice-item:hover .notice-title,
    .notice-item:hover .notice-icon {
        color: #11705d;
    }
</style>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="font-size:15px;">{{ get_phrase('All Notice') }}</h2>

        <a class="btn ol-btn-primary float-end"
            onclick="ajaxModal('{{ route('modal', ['view_path' => 'admin.course.notice.create_notice_board', 'course_id' => $course_details->id]) }}', '{{ get_phrase('Add a new Notice') }}')">
            <i class="fi-rr-plus"></i> {{ get_phrase('New Notice') }}
        </a>
    </div>
    @php
        $notices = App\Models\NoticeBoard::where('course_id', $course_details->id)->get();
    @endphp

    @forelse($notices as $notice)
        <div class="card mb-3 border-0" style="background-color: #e8f8f5; border-radius: 8px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="notice-item"
                        onclick="ajaxModal('{{ route('modal', ['view_path' => 'admin.course.notice.notice', 'id' => $notice->id]) }}','Notice Details')">

                        <div class="notice-header">
                            <i class="fi-rr-bell notice-icon"></i>
                            <h5 class="notice-title">{{ $notice->title }}</h5>
                        </div>

                        <div class="notice-desc">
                            {!! $notice->description !!}
                        </div>
                    </div>

                    <td class="print-d-none">
                        <div class="dropdown ol-icon-dropdown ol-icon-dropdown-transparent">
                            <button class="btn ol-btn-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="fi-rr-menu-dots-vertical"></span>
                            </button>
                            <ul class="dropdown-menu">

                                <li>
                                    <a class="dropdown-item"
                                        onclick="ajaxModal('{{ route('modal', ['view_path' => 'admin.course.notice.edit_notice', 'id' => $notice->id]) }}', '{{ get_phrase('Edit Notice') }}')"
                                        href="javascript:void(0)">Edit
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                        onclick="confirmModal('{{ route('admin.course.notice_resend', ['id' => $notice->id]) }}')"
                                        href="javascript:void(0)">
                                        {{ get_phrase('Resend') }}
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                        onclick="confirmModal('{{ route('admin.course.notice_delete', ['id' => $notice->id]) }}')"
                                        href="javascript:void(0)">{{ get_phrase('Delete') }}</a>
                                </li>

                            </ul>
                        </div>
                    </td>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-muted">No notices found for this course.</p>
    @endforelse
</div>
