@php
    $bundle = App\Models\CourseBundle::where('id', $id)->first();
    $review = App\Models\BundleRating::where('bundle_id', $bundle->id)
        ->where('user_id', auth()->id())
        ->first();
@endphp

@if ($review)
    <!-- Review update form if review already exists -->
    <div class="write-review mb-5">
        <form action="{{ route('course.bundle.rating.update', $review->id) }}" method="POST">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="description">{{ get_phrase('Rate this bundle : ') }}</p>
                <div class="d-flex align-items-center justify-content-end gap-4">
                    <ul class="d-flex gap-1 rating-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <li>
                                <i class="@if ($i <= $review->rating) fa @else fa-regular @endif fa-star rating-star" id="me-{{ $i }}"></i>
                            </li>
                        @endfor
                    </ul>
                    <span class="gradient" id="remove-stars">{{ get_phrase('Remove all') }}</span>
                </div>
            </div>

            <input type="hidden" name="rating" value="{{ $review->rating }}">
            <input type="hidden" name="bundle_id" value="{{ $review->bundle_id }}">
            <textarea type="text" name="comment" class="form-control mb-3" rows="5" placeholder="{{ get_phrase('Write a review...') }}" required>{{ $review->comment }}</textarea>
            <input type="submit" class="eBtn gradient border-none w-100" value="{{ get_phrase('Update') }}">
        </form>
    </div>
@else
    <!-- Display stars for new review -->
    <div class="rating-row text-13 mb-3 px-3">
        @for ($i = 1; $i <= 5; $i++)
            <i class="fas fa-star text-ccc"></i>
        @endfor
    </div>

    <!-- New review form if no review exists -->
    <div class="w-100 px-4 py-3">
        <form action="{{ route('course.bundle.rating.store', ['bundle_id' => $bundle->id]) }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <select id="user_bundle_rating" name="rating" class="form-control" required style="background: #261954; color: #fff;">
                    <option value="">{{ get_phrase('Select rating') }}</option>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ get_phrase('out of') }} 5</option>
                    @endfor
                </select>
            </div>
            <div class="form-group mb-3">
                <textarea id="user_bundle_comment" class="form-control" name="comment" style="background: #261954; color: #fff;" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="eBtn gradient border-none ">{{ get_phrase('Publish Rating') }}</button>
                </div>
            </div>
        </form>
    </div>
@endif

<!-- Jquery Js -->
{{-- <script src="{{ asset('assets/frontend/default/js/jquery-3.7.1.min.js') }}"></script> --}}
<script>
    "use strict";
    $(document).ready(function() {
        let rating_stars = $('.rating-stars i');

        // Handle click event on star rating
        rating_stars.on('click', function(e) {
            e.preventDefault();
            let star = $(this).attr('id').substring(3);
            $('.write-review input[name="rating"]').val(star);

            rating_stars.removeClass('fa').addClass('fa-regular');
            for (let i = 1; i <= star; i++) {
                $('#me-' + i).removeClass('fa-regular').addClass('fa');
            }
        });

        // Handle click event on "Remove all" link
        $('#remove-stars').on('click', function(e) {
            e.preventDefault();
            rating_stars.removeClass('fa fa-regular').addClass('fa-regular');
            $('.write-review input[name="rating"]').val(0);
        });
    });
</script>
