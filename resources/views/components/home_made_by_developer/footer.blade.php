{{-- To make a editable image or text need to be add a "builder editable" class and builder identity attribute with a unique value --}}
{{-- builder identity and builder editable --}}
{{-- builder identity value have to be unique under a single file --}}

@if (get_frontend_settings('recaptcha_status'))
    @push('js')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endpush
@endif

<footer class="footer-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4">
                <div class="footer-content drop-area">
                    <img src="{{ get_image(get_frontend_settings('light_logo')) }}" alt="system logo">
                    <p class="description builder-editable" builder-identity="1">{{ get_phrase('It is a long established fact that a reader will be the distract by the read content of a page layout') }}.</p>

                    <ul class="f-socials d-flex">
                        <li><a href="{{ get_frontend_settings('twitter') }}"><i class="fa-brands fa-x-twitter"></i></a>
                        </li>
                        <li><a href="{{ get_frontend_settings('facebook') }}"><i class="fa-brands fa-facebook-f"></i></a></li>
                        <li><a href="{{ get_frontend_settings('linkedin') }}"><i class="fa-brands fa-linkedin-in"></i></a></li>
                    </ul>
                    <div class="gradient-border2">
                        <a href="{{ route('contact.us') }}" class="gradient-border-btn">
                            {{ get_phrase('Contact with Us') }}
                            <i class="fa-solid fa-arrow-right-long ms-2"></i></a>
                    </div>

                    <a href="{{ get_frontend_settings('mobile_app_link') }}" target="_blank" class="mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="162" height="48" viewBox="0 0 162 48" fill="none">
                            <rect x="0.6" y="0.6" width="160.8" height="46.8" rx="5.4" fill="black" />
                            <rect x="0.6" y="0.6" width="160.8" height="46.8" rx="5.4" stroke="#A6A6A6" stroke-width="1.2" />
                            <path
                                d="M128.324 36.0002H130.563V20.9983H128.324V36.0002ZM148.492 26.4026L145.925 32.9065H145.848L143.184 26.4026H140.772L144.768 35.4926L142.49 40.5494H144.825L150.982 26.4026H148.492ZM135.792 34.2963C135.06 34.2963 134.037 33.9292 134.037 33.0223C134.037 31.8644 135.311 31.4205 136.41 31.4205C137.394 31.4205 137.859 31.6324 138.456 31.9221C138.282 33.312 137.086 34.2963 135.792 34.2963ZM136.064 26.0744C134.442 26.0744 132.764 26.7888 132.069 28.3713L134.056 29.201C134.481 28.3713 135.272 28.1013 136.102 28.1013C137.26 28.1013 138.437 28.7955 138.456 30.0312V30.1854C138.051 29.9538 137.182 29.6065 136.121 29.6065C133.978 29.6065 131.798 30.7834 131.798 32.9838C131.798 34.991 133.554 36.2843 135.522 36.2843C137.027 36.2843 137.859 35.6088 138.378 34.8171H138.456V35.9758H140.619V30.2238C140.619 27.5604 138.629 26.0744 136.064 26.0744ZM122.225 28.2288H119.04V23.0866H122.225C123.899 23.0866 124.85 24.4723 124.85 25.6576C124.85 26.8202 123.899 28.2288 122.225 28.2288ZM122.168 20.9983H116.802V36.0002H119.04V30.3166H122.168C124.649 30.3166 127.089 28.5204 127.089 25.6576C127.089 22.7955 124.649 20.9983 122.168 20.9983ZM92.9098 34.2991C91.3629 34.2991 90.0682 33.0034 90.0682 31.2255C90.0682 29.427 91.3629 28.113 92.9098 28.113C94.4371 28.113 95.6356 29.427 95.6356 31.2255C95.6356 33.0034 94.4371 34.2991 92.9098 34.2991ZM95.481 27.2426H95.4036C94.9011 26.6434 93.934 26.1021 92.7168 26.1021C90.1644 26.1021 87.8253 28.345 87.8253 31.2255C87.8253 34.0863 90.1644 36.3096 92.7168 36.3096C93.934 36.3096 94.9011 35.7681 95.4036 35.1499H95.481V35.8839C95.481 37.8372 94.4371 38.8807 92.7552 38.8807C91.3831 38.8807 90.5324 37.8949 90.184 37.0638L88.2318 37.8757C88.7919 39.2285 90.2802 40.8916 92.7552 40.8916C95.3848 40.8916 97.6081 39.3447 97.6081 35.5746V26.4115H95.481V27.2426ZM99.155 36.0002H101.397V20.9983H99.155V36.0002ZM104.703 31.0512C104.645 29.0791 106.231 28.0741 107.371 28.0741C108.26 28.0741 109.014 28.5189 109.266 29.1565L104.703 31.0512ZM111.662 29.3496C111.237 28.2096 109.942 26.1021 107.293 26.1021C104.664 26.1021 102.479 28.1707 102.479 31.2058C102.479 34.0671 104.645 36.3096 107.544 36.3096C109.883 36.3096 111.237 34.8794 111.798 34.0478L110.058 32.8876C109.478 33.7384 108.686 34.2991 107.544 34.2991C106.404 34.2991 105.592 33.777 105.071 32.7523L111.894 29.9299L111.662 29.3496ZM57.2929 27.6681V29.8333H62.4739C62.3192 31.0512 61.9132 31.9404 61.2945 32.5591C60.5403 33.3128 59.361 34.1444 57.2929 34.1444C54.1029 34.1444 51.6092 31.5733 51.6092 28.3834C51.6092 25.1936 54.1029 22.6221 57.2929 22.6221C59.0136 22.6221 60.2698 23.2989 61.1979 24.1689L62.7256 22.6413C61.43 21.4042 59.7097 20.457 57.2929 20.457C52.9231 20.457 49.25 24.0142 49.25 28.3834C49.25 32.7523 52.9231 36.3096 57.2929 36.3096C59.6511 36.3096 61.43 35.5362 62.8218 34.0863C64.2524 32.6557 64.6972 30.6452 64.6972 29.021C64.6972 28.5189 64.6582 28.0549 64.581 27.6681H57.2929ZM70.5876 34.2991C69.0406 34.2991 67.7066 33.0231 67.7066 31.2058C67.7066 29.3692 69.0406 28.113 70.5876 28.113C72.1339 28.113 73.4679 29.3692 73.4679 31.2058C73.4679 33.0231 72.1339 34.2991 70.5876 34.2991ZM70.5876 26.1021C67.7642 26.1021 65.464 28.248 65.464 31.2058C65.464 34.1444 67.7642 36.3096 70.5876 36.3096C73.4098 36.3096 75.7105 34.1444 75.7105 31.2058C75.7105 28.248 73.4098 26.1021 70.5876 26.1021ZM81.7629 34.2991C80.2171 34.2991 78.8826 33.0231 78.8826 31.2058C78.8826 29.3692 80.2171 28.113 81.7629 28.113C83.3098 28.113 84.6434 29.3692 84.6434 31.2058C84.6434 33.0231 83.3098 34.2991 81.7629 34.2991ZM81.7629 26.1021C78.9406 26.1021 76.6405 28.248 76.6405 31.2058C76.6405 34.1444 78.9406 36.3096 81.7629 36.3096C84.5863 36.3096 86.8864 34.1444 86.8864 31.2058C86.8864 28.248 84.5863 26.1021 81.7629 26.1021Z"
                                fill="white" />
                            <path
                                d="M53.3719 15.8809C52.296 15.8809 51.3706 15.5023 50.6245 14.7563C49.8781 14.0098 49.5 13.0752 49.5 11.9884C49.5 10.9015 49.8782 9.96852 50.6245 9.22038C51.3706 8.47428 52.296 8.09585 53.3719 8.09585C53.92 8.09585 54.4315 8.19052 54.9204 8.38765C55.4094 8.5848 55.8095 8.85848 56.1246 9.21663L56.1989 9.30124L55.3596 10.1403L55.2757 10.0372C55.0668 9.78045 54.801 9.58099 54.4643 9.43601C54.1288 9.29139 53.7613 9.2246 53.3719 9.2246C52.6141 9.2246 51.9854 9.48193 51.4603 10.0051C51.4602 10.0052 51.46 10.0052 51.4598 10.0053C50.9467 10.539 50.6887 11.1888 50.6887 11.9884C50.6887 12.7886 50.947 13.4385 51.4608 13.9723C51.9858 14.4953 52.6145 14.754 53.3719 14.754C54.0655 14.754 54.6392 14.5603 55.0903 14.177H55.0905C55.5104 13.8202 55.7624 13.338 55.8516 12.7234H53.2519V11.6171H56.9752L56.9911 11.7184C57.0216 11.9128 57.0431 12.1005 57.0431 12.2809C57.0431 13.3131 56.7284 14.1557 56.111 14.7755C55.414 15.5144 54.4974 15.8809 53.3719 15.8809L53.3719 15.8809ZM85.702 15.8809C84.6245 15.8809 83.71 15.5021 82.9734 14.7563C82.9734 14.7562 82.9733 14.7562 82.9732 14.7561C82.9731 14.756 82.9731 14.756 82.973 14.7558C82.2354 14.0181 81.8695 13.0832 81.8695 11.9883C81.8695 10.8935 82.2355 9.95859 82.973 9.22084C82.973 9.22076 82.9731 9.22072 82.9732 9.2206L82.9734 9.22036C83.71 8.47444 84.6246 8.09583 85.702 8.09583C86.7774 8.09583 87.6923 8.47454 88.4288 9.23161C89.1665 9.96925 89.5327 10.9024 89.5327 11.9883C89.5327 13.0832 89.1667 14.0181 88.4292 14.7558L88.429 14.7561C87.692 15.5024 86.7687 15.8808 85.702 15.8808L85.702 15.8809ZM58.1086 15.7196V8.2571H58.2286H62.5655V9.38585H59.2767V11.4352H62.243V12.5415H59.2767V14.5927H62.5655V15.7196H58.1086ZM65.2931 15.7196V9.38585H63.2756V8.2571H68.4788V9.38585H68.3588H66.4613V15.7196H65.2931ZM71.8041 15.7196V8.2571H72.9722V8.3771V15.7196H71.8041ZM75.9127 15.7196V9.38585H73.8952V8.2571H79.0983V9.38585H78.9783H77.0808V15.7196H75.9127ZM90.5695 15.7196V8.2571H91.8867L95.239 13.6238L95.2102 12.605V8.2571H96.3783V15.7196H95.2223L91.7088 10.0817L91.7377 11.0998V11.1015V15.7196H90.5695ZM85.702 14.754C86.4598 14.754 87.0784 14.4955 87.5827 13.9733L87.5834 13.9723L87.5841 13.9716C88.0955 13.46 88.3458 12.8015 88.3458 11.9883C88.3458 11.1771 88.0954 10.5164 87.5841 10.005L87.5834 10.0043L87.5827 10.0036C87.0785 9.48144 86.46 9.22453 85.702 9.22453C84.9428 9.22453 84.3243 9.48099 83.8106 10.0031L83.8102 10.0036C83.3082 10.5274 83.0583 11.1774 83.0583 11.9883C83.0583 12.8012 83.3081 13.4491 83.8102 13.973L83.8106 13.9735C84.3244 14.4957 84.943 14.7539 85.702 14.7539V14.754Z"
                                fill="white" />
                            <path d="M24.8617 23.3081L12.0859 36.8681C12.0864 36.8709 12.0874 36.8732 12.0879 36.8761C12.4797 38.3484 13.8245 39.4326 15.4206 39.4326C16.0587 39.4326 16.6577 39.2601 17.1714 38.9573L17.2122 38.9334L31.593 30.6351L24.8617 23.3081Z" fill="#EA4335" />
                            <path d="M37.778 20.9991L37.7657 20.9907L31.5572 17.3915L24.5625 23.6157L31.5815 30.6338L37.7573 27.0704C38.8401 26.4859 39.5751 25.3444 39.5751 24.0281C39.5751 22.7213 38.8499 21.5855 37.778 20.9991Z" fill="#FBBC04" />
                            <path d="M12.0781 11.1319C12.0013 11.4151 11.9609 11.7117 11.9609 12.0197V35.9809C11.9609 36.2883 12.0008 36.586 12.0786 36.8682L25.2941 23.6551L12.0781 11.1319Z" fill="#4285F4" />
                            <path d="M24.9408 24L31.5535 17.3892L17.189 9.06096C16.6669 8.74824 16.0576 8.56776 15.4055 8.56776C13.8094 8.56776 12.4627 9.65388 12.0708 11.1281C12.0703 11.1295 12.0703 11.1305 12.0703 11.1318L24.9408 24Z" fill="#34A853" />
                        </svg>
                    </a>

                </div>
            </div>
            <div class="col-lg-8 col-md-8">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h4>{{ get_phrase('Top Categories') }}</h4>
                            <ul>
                                @foreach (top_categories() as $category)
                                    <li>
                                        <a href="{{ route('courses', $category->slug) }}">
                                            {{ ucfirst($category->title) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h4>{{ get_phrase('Useful links') }}</h4>
                            <ul class=" drop-area">
                                <li><a href="{{ route('courses') }}">{{ get_phrase('Course') }}</a></li>
                                <li><a href="{{ route('blogs') }}">{{ get_phrase('Blog') }}</a></li>
                                <li><a href="{{ route('knowledge.base.topicks') }}">{{ get_phrase('Knowledge Base') }}</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="footer-widget drop-area">
                            <h4>{{ get_phrase('Company') }}</h4>
                            <ul class=" drop-area">
                                <li>
                                    <a href="#">
                                        {{ get_phrase('Phone : ') }}
                                        {{ get_settings('phone') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        {{ get_phrase('Email : ') }}
                                        {{ get_settings('system_email') }}
                                    </a>
                                </li>
                            </ul>
                            <div class="newslater-bottom">
                                <h4 class="builder-editable" builder-identity="2">{{ get_phrase('Newsletter') }}</h4>
                                <p class="description builder-editable" builder-identity="3">{{ get_phrase("Subscribe to stay tuned for new web design and latest updates. Let's do it!") }}</p>
                                <form action="{{ route('newsletter.store') }}" method="post" class="newslater-form" id="newslater-form">
                                    @csrf
                                    <input type="text" name="email" class="form-control" placeholder="{{ get_phrase('Email address') }}">
                                    @if (get_frontend_settings('recaptcha_status'))
                                        <button class="eBtn gradient g-recaptcha" data-sitekey="{{ get_frontend_settings('recaptcha_sitekey') }}" data-callback='onNewslaterSubmit' data-action='submit'>{{ get_phrase('Submit') }}</button>
                                    @else
                                        <button class="eBtn gradient">{{ get_phrase('Submit') }}</button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <ul class="footer-policy">
                        <li><a href="{{ route('about.us') }}">{{ get_phrase('About Us') }}</a></li>
                        <li><a href="{{ route('privacy.policy') }}">{{ get_phrase('Privacy Policy') }}</a></li>
                        <li><a href="{{ route('terms.condition') }}">{{ get_phrase('Terms And Use') }}</a></li>
                        <li><a href="{{ route('refund.policy') }}">{{ get_phrase('Sales and Refunds') }}</a></li>
                        <li><a href="{{ route('cookie.policy') }}">{{ get_phrase('Cookie Policy') }}</a></li>
                        <li><a href="{{ route('faq') }}">{{ get_phrase('FAQ') }}</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <div class="copyright-text">
                        <p class="builder-editable" builder-identity="4">© <a href="{{ get_settings('footer_link') }}" target="_blank">{{ get_settings('footer_text') }}</a> {{ get_phrase('All Rights Reserved') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>


@push('js')
    <script>
        "use strict";

        function onNewslaterSubmit(token) {
            document.getElementById("newslater-form").submit();
        }
    </script>
@endpush
