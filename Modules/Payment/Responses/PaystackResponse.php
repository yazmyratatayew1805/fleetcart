<?php

namespace Modules\Payment\Responses;

use Modules\Order\Entities\Order;
use Modules\Payment\ShouldRedirect;
use Modules\Payment\GatewayResponse;
use Modules\Payment\HasTransactionReference;

class PaystackResponse extends GatewayResponse implements ShouldRedirect, HasTransactionReference
{
    private $order;
    private $clientResponse;

    public function __construct(Order $order, object $clientResponse)
    {
        $this->order = $order;
        $this->clientResponse = $clientResponse;
    }

    public function getOrderId()
    {
        return $this->order->id;
    }

    public function getRedirectUrl()
    {
        return $this->clientResponse->data->authorization_url;
    }

    public function getTransactionReference()
    {
        return $this->clientResponse->query('reference');
    }

    public function toArray()
    {
        return $this->order->toArray() + [
            'redirectUrl' => $this->getRedirectUrl(),
        ];
    }
}
