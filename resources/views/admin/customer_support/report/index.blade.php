@extends('layouts.admin')
@push('title', get_phrase('Customer Support | Reports'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <style>
        .admin-dashboard .apexcharts-toolbar {
            display: none;
        }

        .dashboard-donut .apexcharts-canvas {
            height: 100% !important;
        }

        .admin-dashboard .dashboard-card {
            padding: 10px 20px;
        }

        .admin-dashboard .dashboard-card h3 {
            padding-bottom: 8px;
        }

        .admin-dashboard .dashboard-table tr {
            border-bottom: 1px dashed #dbdfeb;
        }

        .admin-dashboard .dashboard-table thead tr {
            border-bottom: 1px solid #dbdfeb;
        }

        .dashboard-card {
            background: #fff;
            box-shadow: 0 4px 40px 0 var(--shadow);
            border-radius: 6px;
            padding: 20px;
        }

        .dashboard-card h3 {
            font-size: 20px;
            font-weight: 700;
            padding-bottom: 10px;
        }

        .dashboard-card>svg {
            margin-bottom: 20px;
        }

        .dashboard-card>img {
            margin-bottom: 20px;
        }

        .dashboard-card p {
            font-size: 14px;
        }

        td {
            color: #212534 !important;
            font-weight: 500 !important;
        }

        h4,
        .apexcharts-title-text {
            font-size: 16px !important;
        }
    </style>
    <div class="admin-dashboard">
        <div class="row mt-4">
            <div class="col-sm-8">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="dashboard-card mb-3">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.1" width="32" height="32" rx="16" fill="#5B93FF" />
                                <path
                                    d="M23.5 15.0625C23.8075 15.0625 24.0625 14.8075 24.0625 14.5V13.75C24.0625 10.4425 23.0575 9.4375 19.75 9.4375H15.0625V11.125C15.0625 11.4325 14.8075 11.6875 14.5 11.6875C14.1925 11.6875 13.9375 11.4325 13.9375 11.125V9.4375H12.25C8.9425 9.4375 7.9375 10.4425 7.9375 13.75V14.125C7.9375 14.4325 8.1925 14.6875 8.5 14.6875C9.22 14.6875 9.8125 15.28 9.8125 16C9.8125 16.72 9.22 17.3125 8.5 17.3125C8.1925 17.3125 7.9375 17.5675 7.9375 17.875V18.25C7.9375 21.5575 8.9425 22.5625 12.25 22.5625H13.9375V20.875C13.9375 20.5675 14.1925 20.3125 14.5 20.3125C14.8075 20.3125 15.0625 20.5675 15.0625 20.875V22.5625H19.75C23.0575 22.5625 24.0625 21.5575 24.0625 18.25C24.0625 17.9425 23.8075 17.6875 23.5 17.6875C22.78 17.6875 22.1875 17.095 22.1875 16.375C22.1875 15.655 22.78 15.0625 23.5 15.0625ZM15.0625 17.6275C15.0625 17.935 14.8075 18.19 14.5 18.19C14.1925 18.19 13.9375 17.935 13.9375 17.6275V14.3725C13.9375 14.065 14.1925 13.81 14.5 13.81C14.8075 13.81 15.0625 14.065 15.0625 14.3725V17.6275Z"
                                    fill="#5B93FF" />
                            </svg>
                            <h3> {{ count($total_tickets) }} </h3>
                            <p class="text-muted">{{ get_phrase('Total No. Of Tickets') }}</p>
                        </div>
                    </div>
                    @foreach ($ticket_cards as $card)
                        <div class="col-sm-3">
                            <div class="dashboard-card mb-3">
                                <img width="32" height="32" src="{{ asset($card->icon) }}" alt="">
                                @php
                                    $total = App\Models\Ticket::where('status_id', $card->id)->count();
                                @endphp
                                <h3> {{ $total }} </h3>
                                <p class="text-muted">{{ $card->title }} </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- bar chart --}}
                <div class="ol-card mb-4">
                    <div class="ol-card-body p-2 pt-3">
                        <div id="bar-chart"></div>
                    </div>
                </div>

            </div>
            <div class="col-sm-4">
                <div class="ol-card">
                    <div class="ol-card-body p-3">
                        <div class="dashboard-table">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h4> {{ get_phrase('User with most tickets') }} </h4>
                                <a href="{{ route('admin.customer.support.ticket.index') }}" class="btn ol-btn-light view-btn"> {{ get_phrase('View Tickets') }} </a>
                            </div>
                            <table class="table mt-2">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ get_phrase('Name') }}</th>
                                        <th scope="col">{{ get_phrase('Tickets') }}</th>
                                        <th scope="col">{{ get_phrase('Last Reply') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user_tickets as $key => $user_ticket)
                                        <tr>
                                            <td>{{ $user_ticket->user->name }}</td>
                                            <td class="text-center">{{ $user_ticket->total_tickets }}</td>
                                            @php
                                                $lastReply = App\Models\TicketMessage::where('sender_id', $user_ticket->user_id)->latest('created_at')->first();
                                            @endphp
                                            @if ($lastReply)
                                                <td>{{ $lastReply->created_at->format('d M Y, h:i A') }}</td>
                                            @endif

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function() {

            let dataArr = @json($barchart).map(function(status) {

                return {
                    x: status.title,
                    y: status.ticket
                };
            });

            var barOptions = {
                chart: {
                    type: 'bar',
                    height: 460,
                    borderRadius: 5,
                    fontFamily: 'Inter',
                },

                series: [{
                    name: '{{ get_phrase('Total Tickets In This Month') }}',
                    data: dataArr
                }],


                title: {
                    text: 'Total Tickets',
                    align: 'left'
                }
            };

            var barChart = new ApexCharts(document.querySelector("#bar-chart"), barOptions);
            barChart.render();
        });
    </script>

@endpush
