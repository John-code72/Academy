@extends('layouts.admin')
@push('title', get_phrase('Knowledge Sources'))
@push('meta')@endpush
@push('css')@endpush
@section('content')

    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-12px px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-database me-2"></i>
                    {{ get_phrase('Approved knowledge sources') }}
                </h4>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn ol-btn-outline-secondary" id="ingestAllBtn">
                        <span class="fi-rr-download"></span>
                        <span>{{ get_phrase('Ingest all sources') }}</span>
                    </button>
                    <button type="button" class="btn ol-btn-outline-secondary" id="ingestForceBtn">
                        <span class="fi-rr-refresh"></span>
                        <span>{{ get_phrase('Force refresh') }}</span>
                    </button>
                </div>
            </div>
            <p class="mb-0 mt-2 text-muted fs-13px">
                {{ get_phrase('Only whitelisted public or licensed sources are fetched. No unauthorized scraping.') }}
            </p>
        </div>
    </div>

    <div id="ingestStatus" class="alert alert-info d-none"></div>

    <div class="row">
        <div class="col-lg-12">
            <div class="ol-card p-20px">
                <div class="ol-card-body">
                    <div class="table-responsive">
                        <table class="eTable eTable-2 table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ get_phrase('Source') }}</th>
                                    <th>{{ get_phrase('Type') }}</th>
                                    <th>{{ get_phrase('Status') }}</th>
                                    <th>{{ get_phrase('Chunks') }}</th>
                                    <th>{{ get_phrase('Priority') }}</th>
                                    <th>{{ get_phrase('Last fetch') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $doc)
                                    <tr>
                                        <td>{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
                                        <td>
                                            <strong>{{ $doc->name }}</strong>
                                            <div class="text-muted fs-12px">{{ $doc->slug }}</div>
                                            @if ($doc->error_message)
                                                <div class="text-danger fs-12px">{{ Str::limit($doc->error_message, 120) }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $doc->source_type }}</td>
                                        <td><span class="badge bg-secondary">{{ $doc->status }}</span></td>
                                        <td>{{ $doc->chunk_count }}</td>
                                        <td>{{ $doc->priority }}</td>
                                        <td>{{ $doc->last_fetched_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            {{ get_phrase('No ingested documents yet. Run ingestion to populate the knowledge base.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($documents->hasPages())
                        <div class="admin-tInfo-pagi d-flex justify-content-between align-items-center flex-wrap gr-15">
                            <p class="admin-tInfo mb-0">
                                {{ get_phrase('Showing') }} {{ $documents->count() }} {{ get_phrase('of') }} {{ $documents->total() }}
                            </p>
                            {{ $documents->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
<script>
    "use strict";

    function runIngest(force) {
        const statusEl = document.getElementById('ingestStatus');
        statusEl.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
        statusEl.classList.add('alert-info');
        statusEl.textContent = '{{ get_phrase('Ingestion in progress...') }}';

        fetch('{{ route('admin.knowledge.sources.ingest') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ force: !!force }),
        })
            .then((response) => response.json())
            .then((payload) => {
                const summary = payload.summary || {};
                statusEl.classList.remove('alert-info');
                statusEl.classList.add(payload.ok ? 'alert-success' : 'alert-danger');
                statusEl.textContent = `processed=${summary.processed || 0}, skipped=${summary.skipped || 0}, failed=${summary.failed || 0}`;
                if (payload.ok) {
                    setTimeout(() => window.location.reload(), 1200);
                }
            })
            .catch(() => {
                statusEl.classList.remove('alert-info');
                statusEl.classList.add('alert-danger');
                statusEl.textContent = '{{ get_phrase('Ingestion request failed.') }}';
            });
    }

    document.getElementById('ingestAllBtn')?.addEventListener('click', () => runIngest(false));
    document.getElementById('ingestForceBtn')?.addEventListener('click', () => runIngest(true));
</script>
@endpush
