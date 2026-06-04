 @php
     $customFields = App\Models\CustomField::where('course_id', $course_details->id)->orderBy('sorting', 'asc')->get();
     $customTitles = $customFields->pluck('custom_title', 'custom_type')->toArray();
 @endphp
 <div class="mt-5">
     @foreach ($customFields as $field)
         {{-- IMAGE SECTION --}}
         @if ($field->custom_type == 'image')
             <div class="mt-4">
                 <h2 class="g-title">{{ $customTitles['image'] ?? '' }}</h2>
                 <div class="row">
                     @foreach (json_decode($field->custom_field)->data as $image)
                         <div class="col-lg-6 col-md-12 mb-3">
                             <div class="shopCard">
                                 <div class="row g-3">
                                     <div class="col-md-4 col-sm-12">
                                         <figure class="w-100">
                                             <a href="{{ get_all_image('custom-fields/' . $image->file) }}" class="veno-gallery-img  w-100">
                                                 <img src="{{ get_all_image('custom-fields/' . $image->file) }}" class="img-fluid rounded-start w-100 " style="height: 140px;">
                                             </a>
                                         </figure>
                                     </div>
                                     <div class="col-md-8 col-sm-12">
                                         @php
                                             $words = explode(' ', $image->description);
                                             $shortDescription = implode(' ', array_slice($words, 0, 15));
                                         @endphp

                                         <div class="figure-body w-100">
                                             <div class="d-flex justify-content-between">
                                                 <h4 class="text-dark">{{ $image->title }}</h4>
                                             </div>

                                             @if (count($words) > 15)
                                                 <p class="mb-0 name">
                                                     <span class="preview-description">{{ $shortDescription }}...</span>
                                                     <span class="full-descriptions d-none">{{ $image->description }}</span>
                                                     <a href="javascript:void(0);" class="toggle-text toggle-full-text text-primary">{{ get_phrase('See more') }}</a>
                                                     <a href="javascript:void(0);" class="toggle-text toggle-preview-text text-primary d-none">{{ get_Phrase('See less') }}</a>
                                                 </p>
                                             @else
                                                 <p class="mb-2 name">{{ $image->description }}</p>
                                             @endif
                                         </div>

                                     </div>
                                 </div>
                             </div>
                         </div>
                     @endforeach
                 </div>
             </div>
         @elseif($field->custom_type == 'slider')
             {{-- SLIDER SECTION --}}
             <div class="mt-4">
                 <h2 class="g-title">{{ $customTitles['slider'] ?? '' }}</h2>
                 <!-- Banners Slider -->
                 @php $first = true; @endphp
                 <div class="swiper atn-banner-slider mb-30px">
                     <div class="swiper-wrapper">
                         @foreach (json_decode($field->custom_field)->data as $slide)
                             <div class="swiper-slide">
                                 <div class="atn-slide-banner atnAfter">
                                     <img src="{{ get_all_image('custom-fields/' . $slide->file) }}" alt="" style="height: 420px;">
                                     <div class="carousel-caption">
                                         <h5>{{ $slide->title }}</h5>
                                         <p>{{ $slide->description }}</p>
                                     </div>
                                 </div>
                             </div>
                             @php $first = false; @endphp
                         @endforeach

                     </div>
                     <div class="swiper-button-next"></div>
                     <div class="swiper-button-prev"></div>
                     <div class="swiper-pagination"></div>
                 </div>
             </div>
         @elseif($field->custom_type == 'text')
             {{-- TEXT SECTION --}}
             <div class="mt-4">
                 <h2 class="g-title">{{ $customTitles['text'] ?? '' }}</h2>
                 @foreach (json_decode($field->custom_field)->data as $text)
                     <p class="info mb-16"><span> {!! $text->content !!}</span></p>
                 @endforeach
             </div>
         @elseif($field->custom_type == 'video')
             <div class="mt-4">
                 <h2 class="g-title"> {{ $customTitles['video'] ?? '' }}</h2>
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
                                 <div class="ratio ratio-16x9 ">
                                     <iframe src="{{ $embed_url }}" allowfullscreen></iframe>
                                 </div>
                             </div>
                         @endif
                     @endforeach
                 </div>
             </div>
         @elseif($field->custom_type == 'faq')
             <div class="mt-4">
                 <h2 class="g-title">{{ $customTitles['faq'] ?? '' }}</h2>
                 <div class="accordion at-accordion" id="faqAccordion">
                     @php $index = 0; @endphp

                     @foreach ($customFields->where('custom_type', 'faq') as $field)
                         @foreach (json_decode($field->custom_field)->data as $faq)
                             <div class="accordion-item">
                                 <h2 class="accordion-header" id="heading{{ $index }}">
                                     <button class="f-16 accordion-button {{ $index > 0 ? 'collapsed' : '' }} text-start fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                         aria-controls="collapse{{ $index }}"> {{ $faq->question }}
                                     </button>
                                 </h2>
                                 <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#faqAccordion">
                                     <div class="accordion-body text-muted d-flex flex-column gap-2">
                                         <p class="mb-2">{{ $faq->answer }}</p>
                                     </div>
                                 </div>
                             </div>
                             @php $index++; @endphp
                         @endforeach
                     @endforeach
                 </div>
             </div>
         @elseif($field->custom_type == 'gallery')
             <div class="mt-4">
                 <h2 class="g-title">{{ $customTitles['gallery'] ?? '' }}</h2>
                 <div class="row g-3">
                     @foreach ($customFields->where('custom_type', 'gallery') as $field)
                         @foreach (json_decode($field->custom_field)->data as $image)
                             <div class="col-lg-3">
                                 <div class="card gsCard">
                                     <a href="{{ get_all_image('custom-fields/' . $image->file) }}" class="veno-gallery-img  ">
                                         <img src="{{ get_all_image('custom-fields/' . $image->file) }}" class="img-fluid rounded-start w-100" style="height: 260px; object-fit: cover; border-radius: 5px;" alt="">
                                     </a>
                                 </div>
                             </div>
                         @endforeach
                     @endforeach
                 </div>
             </div>
         @endif
     @endforeach

 </div>


 {{-- Custom Field  --}}
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         document.querySelectorAll('.toggle-full-text').forEach(function(btn) {
             btn.addEventListener('click', function() {
                 const container = this.closest('.name');
                 container.querySelector('.preview-description').classList.add('d-none');
                 container.querySelector('.full-descriptions').classList.remove('d-none');
                 container.querySelector('.toggle-full-text').classList.add('d-none');
                 container.querySelector('.toggle-preview-text').classList.remove('d-none');
             });
         });

         document.querySelectorAll('.toggle-preview-text').forEach(function(btn) {
             btn.addEventListener('click', function() {
                 const container = this.closest('.name');
                 container.querySelector('.full-descriptions').classList.add('d-none');
                 container.querySelector('.preview-description').classList.remove('d-none');
                 container.querySelector('.toggle-preview-text').classList.add('d-none');
                 container.querySelector('.toggle-full-text').classList.remove('d-none');
             });
         });
     });
 </script>
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         // Initialize Swiper
         var swiper = new Swiper(".atn-banner-slider", {
             loop: true,
             autoplay: {
                 delay: 3000,
                 disableOnInteraction: false,
             },
             pagination: {
                 el: ".swiper-pagination",
                 clickable: true,
             },
             navigation: {
                 nextEl: ".swiper-button-next",
                 prevEl: ".swiper-button-prev",
             },
         });
     });
 </script>

 {{-- Custom Field  --}}
