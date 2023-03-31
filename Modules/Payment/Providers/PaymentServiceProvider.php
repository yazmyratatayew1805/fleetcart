<?php

namespace Modules\Payment\Providers;

use Modules\Payment\Gateways\AuthorizeNet;
use Modules\Payment\Gateways\COD;
use Modules\Payment\Gateways\Paystack;
use Modules\Payment\Gateways\Paytm;
use Modules\Payment\Facades\Gateway;
use Modules\Payment\Gateways\PayPal;
use Modules\Payment\Gateways\Stripe;
use Modules\Payment\Gateways\Razorpay;
use Illuminate\Support\ServiceProvider;
use Modules\Payment\Gateways\Instamojo;
use Modules\Payment\Gateways\BankTransfer;
use Modules\Payment\Gateways\CheckPayment;
use Barryvdh\Omnipay\Facade;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias(Barryvdh\Omnipay\Facade::class, 'Omnipay');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        $this->registerPayPalExpress();
        $this->registerStripe();
        $this->registerPaytm();
        $this->registerRazorpay();
        $this->registerInstamojo();
        $this->registerPaystack();
        $this->registerAuthorizenet();
        $this->registerCashOnDelivery();
        $this->registerBankTransfer();
        $this->registerCheckPayment();
    }

    private function enabled($paymentMethod)
    {
        if (app('inAdminPanel')) {
            return true;
        }

        return setting("{$paymentMethod}_enabled");
    }

    private function registerPayPalExpress()
    {
        if ($this->enabled('paypal')) {
            Gateway::register('paypal', new PayPal());
        }
    }

    private function registerStripe()
    {
        if ($this->enabled('stripe')) {
            Gateway::register('stripe', new Stripe());
        }
    }

    private function registerPaytm()
    {
        if ($this->enabled('paytm')) {
            Gateway::register('paytm', new Paytm());
        }
    }

    private function registerRazorpay()
    {
        if ($this->enabled('razorpay')) {
            Gateway::register('razorpay', new Razorpay());
        }
    }

    private function registerInstamojo()
    {
        if ($this->enabled('instamojo')) {
            Gateway::register('instamojo', new Instamojo());
        }
    }

    private function registerPaystack()
    {
        if ($this->enabled('paystack')) {
            Gateway::register('paystack', new Paystack());
        }
    }

    private function registerAuthorizenet()
    {
        if ($this->enabled('authorizenet')) {
            Gateway::register('authorizenet', new AuthorizeNet());
        }
    }

    private function registerCashOnDelivery()
    {
        if ($this->enabled('cod')) {
            Gateway::register('cod', new COD());
        }
    }

    private function registerBankTransfer()
    {
        if ($this->enabled('bank_transfer')) {
            Gateway::register('bank_transfer', new BankTransfer());
        }
    }

    private function registerCheckPayment()
    {
        if ($this->enabled('check_payment')) {
            Gateway::register('check_payment', new CheckPayment());
        }
    }
}
