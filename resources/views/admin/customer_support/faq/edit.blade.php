<form action="{{ route('admin.customer.support.ticket.faq.update', ['id' => $faq->id]) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="question">{{ get_phrase('Question') }}<span class="required">*</span></label>
        <textarea name="question" rows="2" class="form-control ol-form-control" placeholder="Enter your question here" id="question" required>{{ $faq->question }}</textarea>
    </div>

    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="answer">{{ get_phrase('Answer') }}<span class="required">*</span></label>
        <textarea name="answer" rows="3" class="form-control ol-form-control text_editor" placeholder="Enter your answer here" id="answer" required>{{ $faq->answer }}</textarea>
    </div>
    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Update') }}</button>
    </div>
</form>
