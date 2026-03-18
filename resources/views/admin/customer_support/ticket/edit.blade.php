<form action="{{ route('admin.customer.support.ticket.update', ['id' => $ticket->id]) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="subject">{{ get_phrase('Subject') }}</label>
        <input class="form-control ol-form-control" type="text" id="subject" name="subject" value="{{ $ticket->subject }}" required>
    </div>

    <div class="fpb-7 mb-3">
        <label for="select_category_id" class="form-label ol-form-label">{{ get_phrase('Select Category') }}</label>
        <select class="form-control ol-form-control ol-select2" name="category_id" id="select_category_id" required>
            <option value="">{{ get_phrase('Select a category') }}</option>
            @php
                $all_categories = App\Models\TicketCategory::where('status', 1)->get();
            @endphp
            @foreach ($all_categories as $all_category)
                <option value="{{ $all_category->id }}" {{ $ticket->category_id == $all_category->id ? 'selected' : '' }}>{{ $all_category->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="fpb-7 mb-3">
        <label for="select_user_id" class="form-label ol-form-label">{{ get_phrase('Select User') }}</label>
        <select class="form-control ol-form-control ol-select2" name="user_id" id="select_user_id" required>
            <option value="">{{ get_phrase('Select a user') }}</option>
            @php
                $all_users = App\Models\User::whereNot('role', 'admin')->get();
            @endphp
            @foreach ($all_users as $all_user)
                <option value="{{ $all_user->id }}" {{ $ticket->user_id == $all_user->id ? 'selected' : '' }}>{{ $all_user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="fpb-7 mb-3">
        <label for="select_status_id" class="form-label ol-form-label">{{ get_phrase('Select Status') }}</label>
        <select class="form-control ol-form-control ol-select2" name="status_id" id="select_status_id" required>
            <option value="">{{ get_phrase('Select a status') }}</option>
            @php
                $all_statuses = App\Models\TicketStatus::where('status', 1)->get();
            @endphp
            @foreach ($all_statuses as $all_status)
                <option value="{{ $all_status->id }}" {{ $ticket->status_id == $all_status->id ? 'selected' : '' }}>{{ $all_status->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="fpb-7 mb-3">
        <label for="select_priority_id" class="form-label ol-form-label">{{ get_phrase('Select Priority') }}</label>
        <select class="form-control ol-form-control ol-select2" name="priority_id" id="select_priority_id" required>
            <option value="">{{ get_phrase('Select a priority') }}</option>
            @php
                $all_priorities = App\Models\TicketPriority::where('status', 1)->get();
            @endphp
            @foreach ($all_priorities as $all_priority)
                <option value="{{ $all_priority->id }}" {{ $ticket->priority_id == $all_priority->id ? 'selected' : '' }}>{{ $all_priority->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Update') }}</button>
    </div>
</form>
<script>
    "use strict";
    $(".ol-select2").select2({
        dropdownParent: $('#ajaxModal')
    });
    $(".ol-modal-niceSelect").niceSelect({
        dropdownParent: $('#ajaxModal')
    });
</script>
