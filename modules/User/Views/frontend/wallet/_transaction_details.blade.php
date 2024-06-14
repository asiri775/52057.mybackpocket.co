<?php
if($transaction!=null){
    $refId = $transaction->getRefId();
    switch($transaction->type){
        case \App\Helpers\Constants::TRANSACTION_TYPE_EARNINGS:
        $booking = \Modules\Booking\Models\Booking::where('id', $refId)->first();
        if($booking!=null){
        ?>
<table class="table demo-table-search table-responsive-block data-table table-two-info">
    <tr>
        <th>Booking Reference</th>
        <td><a class="n-link" href="{{ route('user.single.booking.detail', $booking->id) }}">#{{ $booking->id }}</a></td>
    </tr>
    <tr>
        <th>Booking Date</th>
        <td>{{ \App\Helpers\CodeHelper::formatDateTime($booking->start_date) }}</td>
    </tr>
    <tr>
        <th>Gross Sale Amount</th>
        <td>{{ \App\Helpers\CodeHelper::formatPrice($booking->payable_amount) }}</td>
    </tr>
    <tr>
        <th>Site Booking Fee</th>
        <td>{{ \App\Helpers\CodeHelper::formatPrice($booking->extra_fee) }}</td>
    </tr>
    <tr>
        <th>Net Earnings</th>
        <td>{{ \App\Helpers\CodeHelper::formatPrice($booking->host_amount) }}</td>
    </tr>
    <tr>
        <th>Transaction / Reference ID</th>
        <td>#{{ $transaction->id }}</td>
    </tr>
</table>
<?php
        }
        break;
        case \App\Helpers\Constants::TRANSACTION_TYPE_DEPOSIT:
        break;
        case \App\Helpers\Constants::TRANSACTION_TYPE_WITHDRAWAL:
        $withdrawal = \Modules\Vendor\Models\VendorPayout::where('id', $refId)->first();
        if($withdrawal!=null){
        ?>
<table class="table demo-table-search table-responsive-block data-table table-two-info">
    <tr>
        <th>Status</th>
        <td><span class="badge badge-warning">{{ucwords($withdrawal->status)}}</span></td>
    </tr>
    <tr>
        <th>Amount</th>
        <td>{{ \App\Helpers\CodeHelper::formatPrice($withdrawal->amount) }}</td>
    </tr>
    <tr>
        <th>Date of Request</th>
        <td>{{ \App\Helpers\CodeHelper::formatDateTime($withdrawal->created_at) }}</td>
    </tr>
    <tr>
        <th>Date of Completion</th>
        <td>{{ \App\Helpers\CodeHelper::formatDateTime($withdrawal->pay_date, true, 'Not Completed Yet') }}</td>
    </tr>
    <tr>
        <th>Deposit Account</th>
        <td>{{ $withdrawal->account_info }}</td>
    </tr>
    <tr>
        <th>Fee</th>
        <td>{{ \App\Helpers\CodeHelper::formatPrice($withdrawal->fee) }}</td>
    </tr>
    <tr>
        <th>Transaction / Reference ID</th>
        <td>#{{ $transaction->id }}</td>
    </tr>
</table>
<?php
        }
        break;
        case \App\Helpers\Constants::TRANSACTION_TYPE_WITHDRAWAL_CANCELLED:
        break;
    }
    ?>
<?php
}else{
    ?>
<p>No Transaction Found</p>
<?php
}
?>
