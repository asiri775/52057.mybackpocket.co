<?php

namespace Modules\Booking\Models;

use App\BaseModel;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Tour\Models\Tour;
use Modules\User\Emails\CreditPaymentEmail;
use Modules\User\Emails\VendorRegisteredEmail;
use Modules\User\Models\Wallet\Transaction;

class Payment extends BaseModel
{
    protected $table = 'bravo_booking_payments';
    protected $meta_json = null;

    public function statusClass()
    {
        $statusClass = "";
        switch ($this->status) {
            case 'paid':
                $statusClass = "complete";
                break;
            case 'draft':
                $statusClass = "pending";
                break;
            case 'fail':
                $statusClass = "cancelled";
                break;
        }
        return $statusClass;
    }

    public function statusText()
    {
        switch ($this->status) {
            case 'paid':
                $payment_status = "PAID";
                break;
            case 'draft':
                $payment_status = "UNPAID";
                break;
            case 'fail':
                $payment_status = "FAIL";
                break;
            default:
                $payment_status = "UNPAID";
                break;
        }
        return $payment_status;
    }

    public function save(array $options = [])
    {
        if (empty($this->code))
            $this->code = $this->generateCode();
        return parent::save($options); // TODO: Change the autogenerated stub
    }

    public function getStatusNameAttribute()
    {
        return booking_status_to_text($this->status);
    }

    public function getGatewayObjAttribute()
    {
        return $this->payment_gateway ? get_payment_gateway_obj($this->payment_gateway) : false;
    }

    public function generateCode()
    {
        return md5(uniqid() . rand(0, 99999));
    }

    public function notifyObject()
    {
        switch ($this->object_model) {
            case "wallet_deposit":
                $user = User::find($this->object_id);
                if ($this->status != 'completed') {
                    $url = route('user.wallet');
                    return [false, __("Payment fail"), $url];
                }
                if (!empty($user)) {
                    try {
                        $user->creditPaymentUpdate($this);
                    } catch (\Exception $exception) {
                        $url =  route('user.wallet');
                        return [false, $exception->getMessage(), $url];
                    }

                    $url = route('user.wallet');
                    return [true, __("Payment updated"), $url];
                }

                break;
        }
    }

    public function markAsFailed($logs = '')
    {
        $this->status = 'fail';
        $this->logs = \GuzzleHttp\json_encode($logs);
        $this->save();
        $this->sendUpdatedPurchaseEmail();
        return $this->notifyObject();
    }
    public function markAsCancel($logs = '')
    {
        $this->status = 'cancel';
        $this->logs = \GuzzleHttp\json_encode($logs);
        $this->save();
        $this->sendUpdatedPurchaseEmail();
        return $this->notifyObject();
    }

    public function markAsCompleted($logs = '')
    {
        $this->status = 'completed';
        $this->logs = \GuzzleHttp\json_encode($logs);
        $this->save();
        $this->sendNewPurchaseEmail();
        return $this->notifyObject();
    }

    public function getMeta($key = '')
    {

        if ($this->meta_json === null) {
            $this->meta_json = (array) json_decode($this->meta, true);
        }
        if (empty($key)) return $this->meta_json;
        return $this->meta_json[$key] ?? null;
    }



    public function sendUpdatedPurchaseEmail()
    {

        switch ($this->object_model) {
            case "wallet_deposit":
                Mail::to(setting_item('admin_email'))->send(new CreditPaymentEmail(false, $this, 'admin'));
                if ($this->user)
                    Mail::to($this->user->email)->send(new CreditPaymentEmail(false, $this, 'customer'));
                break;
        }
    }

    public function sendNewPurchaseEmail()
    {

        switch ($this->object_model) {
            case "wallet_deposit":
                Mail::to(setting_item('admin_email'))->send(new CreditPaymentEmail(true, $this, 'admin'));

                if ($this->user)
                    Mail::to($this->user->email)->send(new CreditPaymentEmail(true, $this, 'customer'));
        }
    }
}
