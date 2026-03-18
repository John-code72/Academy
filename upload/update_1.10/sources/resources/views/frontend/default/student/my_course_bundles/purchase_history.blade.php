<div class="row">
    <div class="col-md-12">
        <ul class="purchase-history-list">
            <li class="purchase-history-list-header border-bottom-0">
                <div class="row">
                    <div class="col-md-12 py-3">
                        <h4>
                            {{ $bundle->title }}
                        </h4>
                    </div>
                </div>
            </li>
            @foreach ($purchase_histories as $purchase)
                <li class="purchase-history-items mb-2 border-bottom-0 border-top">
                    <div class="row">

                        <div class="col-md-12 py-3">
                            <div class="row">
                                <div class="col-sm-4">{{ get_phrase('Date') }}</div>
                                <div class="col-sm-3">{{ get_phrase('Total price') }}</div>
                                <div class="col-sm-3">{{ get_phrase('Payment type') }}</div>
                                <div class="col-sm-2">{{ get_phrase('Actions') }}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="col-md-12 py-3">
                            <div class="row">
                                <div class="col-sm-4">
                                    {{ date('d M Y', $purchase->created_at->timestamp) }}
                                </div>
                                <div class="col-sm-3"><b>
                                        {{ currency($purchase['amount']) }}
                                    </b></div>
                                <div class="col-sm-3">
                                    {{ ucfirst($purchase['payment_method']) }}
                                </div>
                                <div class="col-sm-2">
                                    <a href="{{ route('my.course.bundle.invoice', $bundle->id) }}">
                                        {{ get_phrase('Invoice') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
