<?php

namespace Modules\Payment\Gateways;

use stdClass;
use Exception;
use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Responses\InstamojoResponse;
use Modules\Payment\Responses\PaystackResponse;

class Paystack implements GatewayInterface
{
    public $label;
    public $description;

    public function __construct()
    {
        $this->label = setting('paystack_label');
        $this->description = setting('paystack_description');
    }

    public function purchase(Order $order, Request $request)
    {
        if (currency() !== 'USD' && currency() !== 'NGN') {
            throw new Exception(trans('payment::messages.only_supports_ngn_and_usd'));
        }

        $url = 'https://api.paystack.co/transaction/initialize';

        $fields = [
            'email' => $order->customer_email,
            'currency' => currency(),
            'amount' => $order->total->convertToCurrentCurrency()->subunit(),
            'callback_url' => $this->getRedirectUrl($order),
        ];

        $fields_string = http_build_query($fields);

        try {
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . setting('paystack_secret_key'), 'Cache-Control: no-cache']);

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);
        } catch (Exception $e) {
            throw new Exception(trim(trim($e->getMessage()), '"'));
        }

        $response = json_decode($result);

        if (!$response->status) {
            throw new Exception($response->message);
        }

        return new PaystackResponse($order, $response);
    }

    private function getRedirectUrl($order)
    {
        return route('checkout.complete.store', ['orderId' => $order->id, 'paymentMethod' => 'paystack']);
    }

    public function complete(Order $order)
    {
        return new PaystackResponse($order, request());
    }
}
