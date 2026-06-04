@extends('layouts.default')
@push('title', get_phrase('Defrilex onboarding Test'))
@push('css')
    <style>
        .defrilex-onboarding-test-page {
            background: #f6f8fc;
        }

        .defrilex-onboarding-test-page .defrilex-onboarding-test-shell {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(17, 24, 39, 0.06);
            overflow: hidden;
            padding: 24px;
        }

        .defrilex-onboarding-test-page .container {
            max-width: 980px;
        }

        .defrilex-onboarding-test-page .quiz-title {
            color: #1f2937;
        }

        .defrilex-onboarding-test-page .quiz-starter {
            margin-left: 0;
            margin-right: 0;
        }

        .defrilex-onboarding-test-page .quiz-wrap {
            padding-left: 8px;
            padding-right: 8px;
        }

        .defrilex-onboarding-test-page .quiz-wrap .px-4 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        @media (max-width: 768px) {
            .defrilex-onboarding-test-page .defrilex-onboarding-test-shell {
                padding: 16px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="pt-4 pb-5 defrilex-onboarding-test-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="defrilex-onboarding-test-shell">
                        <div class="quiz-wrap">
                            @include('course_player.practice_test.index')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
