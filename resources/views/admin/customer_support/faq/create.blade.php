<form action="{{ route('admin.customer.support.ticket.faq.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="question">{{ get_phrase('Question') }}<span class="required">*</span></label>
        <textarea name="question" rows="2" class="form-control ol-form-control" placeholder="Enter your question here" id="question" required></textarea>
    </div>

    <div class="fpb7 mb-2">
        <label class="form-label ol-form-label" for="answer">{{ get_phrase('Answer') }}<span class="required">*</span></label>
        <textarea name="answer" rows="3" class="form-control ol-form-control text_editor" placeholder="Enter your answer here" id="answer" required></textarea>
    </div>
    <div class="fpb7 mb-2">
        <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Add faq') }}</button>
    </div>
</form>
