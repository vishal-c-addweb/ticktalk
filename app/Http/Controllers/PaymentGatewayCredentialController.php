<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\PaymentGateway\UpdateGatewayCredentials;
use App\Models\Currency;
use App\Models\OfflinePaymentMethod;
use App\Models\PaymentGatewayCredentials;

class PaymentGatewayCredentialController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.paymentGatewayCredential';
        $this->activeSettingMenu = 'payment_gateway_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_payment_setting') == 'all'));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->credentials = PaymentGatewayCredentials::first();
        $this->offlineMethods = OfflinePaymentMethod::all();
        $this->currencies = Currency::all();

        $this->view = 'payment-gateway-settings.ajax.paypal';

        $tab = request('tab');

        switch ($tab) {
        case 'stripe':
            $this->view = 'payment-gateway-settings.ajax.stripe';
                break;
        case 'razorpay':
            $this->view = 'payment-gateway-settings.ajax.razorpay';
                break;
        case 'offline':
            $this->view = 'payment-gateway-settings.ajax.offline';
                break;
        default:
            $this->view = 'payment-gateway-settings.ajax.paypal';
                break;
        }

        ($tab == '') ? $this->activeTab = 'paypal' : $this->activeTab = $tab;

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('payment-gateway-settings.index', $this->data);
    }

    /**
     * @param UpdateGatewayCredentials $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateGatewayCredentials $request, $id)
    {
        $credential = PaymentGatewayCredentials::findOrFail($id);

        if ($request->payment_method == 'paypal') {
            $credential->paypal_mode = $request->paypal_mode;

            if($request->paypal_mode == 'sandbox') {
                $credential->sandbox_paypal_client_id = $request->sandbox_paypal_client_id;
                $credential->sandbox_paypal_secret = $request->sandbox_paypal_secret;
            }
            else {
                $credential->paypal_client_id = $request->paypal_client_id;
                $credential->paypal_secret = $request->paypal_secret;
            }

            ($request->paypal_status) ? $credential->paypal_status = 'active' : $credential->paypal_status = 'deactive';
        }

        if ($request->payment_method == 'stripe') {

            if($request->stripe_mode == 'test') {
                $credential->test_stripe_client_id = $request->test_stripe_client_id;
                $credential->test_stripe_secret = $request->test_stripe_secret;
                $credential->test_stripe_webhook_secret = $request->test_stripe_webhook_secret;
            }
            else {
                $credential->live_stripe_client_id = $request->live_stripe_client_id;
                $credential->live_stripe_secret = $request->live_stripe_secret;
                $credential->live_stripe_webhook_secret = $request->live_stripe_webhook_secret;
            }

            $credential->stripe_mode = $request->stripe_mode;
            ($request->stripe_status) ? $credential->stripe_status = 'active' : $credential->stripe_status = 'deactive';
        }

        if ($request->payment_method == 'razorpay') {

            if($request->razorpay_mode == 'test') {
                $credential->test_razorpay_key = $request->test_razorpay_key;
                $credential->test_razorpay_secret = $request->test_razorpay_secret;
            }
            else {
                $credential->live_razorpay_key = $request->live_razorpay_key;
                $credential->live_razorpay_secret = $request->live_razorpay_secret;
            }

            $credential->razorpay_mode = $request->razorpay_mode;
            ($request->razorpay_status) ? $credential->razorpay_status = 'active' : $credential->razorpay_status = 'inactive';
        }

        $credential->save();

        return Reply::success(__('messages.settingsUpdated'));
    }

}
