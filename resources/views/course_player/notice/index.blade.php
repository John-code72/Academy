<div class="tab-pane fade @if ($tab == 'notice') show active @endif" id="pills-notice" role="tabpanel" aria-labelledby="pills-notice-tab" tabindex="0">
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
        /* line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden; */
    }

    .notice-item:hover .notice-title,
    .notice-item:hover .notice-icon {
        color: #11705d;
    }
</style>
<div class="container my-4">
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
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-muted">{{ get_phrase('No notices found for this course.') }}</p>
    @endforelse
</div>
</div>



